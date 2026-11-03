<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Integration;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ProdumanApi\Builder;
use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Request\Operations\CreateSell;
use ProdumanApi\Request\Orders\CreateRequest;
use ProdumanApi\Request\Orders\ListRequest;
use ProdumanApi\Request\Orders\Model\PositionModel;
use ProdumanApi\Request\Webhooks\EndpointCreateRequest;
use ProdumanApi\Tests\Mock\MockPsrLogger;
use ProdumanApi\Tests\Support\LocalServer;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

#[Group('integration')]
final class HttpTransportTest extends TestCase
{
    private ?LocalServer $server = null;
    private array $environment = [];
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->originalEnv = $_ENV;
        foreach (['PRODUMAN_API_URL', 'HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy', 'NO_PROXY', 'no_proxy'] as $name) {
            $this->environment[$name] = getenv($name);
            putenv($name);
        }
        unset($_ENV['PRODUMAN_API_URL']);
        putenv('NO_PROXY=127.0.0.1,localhost');
        try {
            $this->server = new LocalServer();
            putenv('PRODUMAN_API_URL=' . $this->server->url . '/kassa');
        } catch (\Throwable $error) {
            $this->restoreEnvironment();
            throw $error;
        }
    }

    protected function tearDown(): void
    {
        try {
            $this->server?->stop();
        } finally {
            $this->restoreEnvironment();
        }
    }

    private function restoreEnvironment(): void
    {
        foreach ($this->environment as $name => $value) {
            putenv(false === $value ? $name : $name . '=' . $value);
        }
        $_ENV = $this->originalEnv;
    }

    public function testPublicOrderMethodsOverCurl(): void
    {
        $client = Builder::buildClient('fixture-token', 'fixture-app', 'fixture-secret');
        $position = new PositionModel();
        $position->name = 'Товар';
        $position->itemCode = '000123';
        $position->quantity = 1;
        $position->price = 100;
        $position->excisable = false;
        $request = new CreateRequest();
        $request->positions = [$position];
        self::assertSame('000123', $client->orders->create($request)->positions[0]->itemCode);
        self::assertSame(201, $client->getLastStatusCode());
        self::assertSame('000123', $client->orders->update('order-1', $request)->positions[0]->itemCode);
        self::assertSame('order-1', $client->orders->get('order-1')->id);
        $filter = new ListRequest();
        $filter->limit = 2;
        self::assertSame('000123', $client->orders->list($filter)->items[0]->positions[0]->itemCode);
        $client->orders->delete('order-1');
        self::assertSame(204, $client->getLastStatusCode());
        self::assertSame('9', $client->getLastXRateLimitRemaining());
        self::assertSame('10', $client->getLastXRateLimitLimit());
        $requests = $this->server->requests();
        self::assertSame(['POST', 'PUT', 'GET', 'GET', 'DELETE'], array_column($requests, 'method'));
        self::assertSame('fixture-app', $requests[0]['headers']['x-app-id']);
        self::assertSame('fixture-secret', $requests[0]['headers']['x-app-secret']);
        self::assertSame('fixture-token', $requests[0]['headers']['x-client-token']);
        self::assertSame('application/json', $requests[0]['headers']['content-type']);
        self::assertSame('2', $requests[3]['query']['limit']);
        $sent = json_decode($requests[0]['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('000123', $sent['positions'][0]['itemCode']);
        self::assertFalse($sent['positions'][0]['excisable']);
    }

    public function testApplicationClientSendsWebhookWithoutLoggingSecret(): void
    {
        $logger = new MockPsrLogger();
        $client = Builder::buildApplicationClient('fixture-app', 'fixture-secret', 5, $logger);
        $request = new EndpointCreateRequest();
        $request->url = 'https://fixture.test/webhook';
        $request->authType = 'BEARER_TOKEN';
        $request->secret = 'fixture-webhook-secret';
        self::assertSame('endpoint-1', $client->webhooks->endpointCreate($request)->id);
        $received = $this->server->requests()[0];
        self::assertArrayNotHasKey('x-client-token', $received['headers']);
        self::assertSame('fixture-webhook-secret', json_decode($received['body'], true, 512, JSON_THROW_ON_ERROR)['secret']);
        $logs = json_encode($logger->getLogs(), JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('fixture-webhook-secret', $logs);
        self::assertStringNotContainsString('fixture-secret', $logs);
    }

    #[TestWith([301])]
    #[TestWith([302])]
    #[TestWith([303])]
    #[TestWith([307])]
    #[TestWith([308])]
    public function testRedirectDoesNotSendCredentialsToAnotherHost(int $status): void
    {
        $client = Builder::buildClient('fixture-token', 'fixture-app', 'fixture-secret');
        try {
            $client->orders->get('redirect?status=' . $status);
            self::fail('Перенаправление должно быть запрещено');
        } catch (HttpException $error) {
            self::assertInstanceOf(HttpExceptionInterface::class, $error->getPrevious());
        }
        self::assertSame($status, $client->getLastStatusCode());
        self::assertCount(1, $this->server->requests());
        self::assertSame([], $this->server->requests('capture'));
    }

    #[TestWith([400])]
    #[TestWith([429])]
    #[TestWith([500])]
    public function testHttpErrorsThroughPublicMethods(int $status): void
    {
        $client = Builder::buildClient('fixture-token', 'fixture-app', 'fixture-secret');
        try {
            $client->orders->get('error-' . $status);
            self::fail('Ожидалась ошибка сервера');
        } catch (\RuntimeException $error) {
            self::assertInstanceOf($status < 500 ? ApiException::class : HttpException::class, $error);
            self::assertInstanceOf(HttpExceptionInterface::class, $error->getPrevious());
        }
        self::assertSame($status, $client->getLastStatusCode());
        self::assertSame('9', $client->getLastXRateLimitRemaining());
        self::assertCount(1, $this->server->requests());
    }

    public function testMalformedSuccessIsAJsonResponseException(): void
    {
        $client = Builder::buildClient('fixture-token', 'fixture-app', 'fixture-secret');
        try {
            $client->orders->get('malformed');
            self::fail('Ожидалась ошибка JSON');
        } catch (JsonResponseException $error) {
            self::assertInstanceOf(\JsonException::class, $error->getPrevious());
        }
        self::assertSame(200, $client->getLastStatusCode());
    }

    #[TestWith(['error-500'])]
    #[TestWith(['timeout'])]
    public function testPostIsNotRetried(string $cashbox): void
    {
        $client = Builder::buildClient('fixture-token', 'fixture-app', 'fixture-secret', 1);
        $request = new CreateSell();
        $request->cashboxId = $cashbox;
        try {
            $client->operations->create($request);
            self::fail('Ожидалась ошибка операции');
        } catch (HttpException $error) {
            self::assertNotNull($error->getPrevious());
        }
        $this->server->waitForPendingRequests();
        self::assertCount(1, $this->server->requests());
        self::assertSame('POST', $this->server->requests()[0]['method']);
    }
}
