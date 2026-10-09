<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\ClientHelper;

use PHPUnit\Framework\TestCase;
use ProdumanApi\ClientHelper\Operations;
use ProdumanApi\ClientHelper\Orders;
use ProdumanApi\Interfaces\ApiHttpClientInterface;
use ProdumanApi\Request\Operations\CreateBuy;
use ProdumanApi\Request\Operations\CreateSell;
use ProdumanApi\Request\Operations\Model\DetailsBuyModel;
use ProdumanApi\Request\Operations\Model\DetailsSellModel;
use ProdumanApi\Request\Orders\CreateRequest;
use ProdumanApi\Request\Orders\Model\PositionModel;

final class ItemCodeTest extends TestCase
{
    /**
     * @dataProvider codes
     */
    public function testOrderHelpersPreserveCodeInRequestsAndResponses(?string $code): void
    {
        $position = new PositionModel();
        $position->name = 'Товар';
        $position->itemCode = $code;
        $request = new CreateRequest();
        $request->positions = [$position];
        $response = ['id' => 'order-id', 'positions' => [['id' => 'position-id', 'itemCode' => $code]]];
        $api = $this->createMock(ApiHttpClientInterface::class);
        $api->expects($this->exactly(4))->method('request')->willReturnCallback(
            function (string $method, string $action, array $query, string $body) use ($code, $response): string {
                if ('GET' !== $method) {
                    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                    $this->assertSame($code, $data['positions'][0]['itemCode']);
                }
                return json_encode('GET' === $method && 'orders' === $action ? ['items' => [$response]] : $response, JSON_THROW_ON_ERROR);
            }
        );
        $orders = new Orders($api);
        $this->assertSame($code, $orders->create($request)->positions[0]->itemCode);
        $this->assertSame($code, $orders->update('order-id', $request)->positions[0]->itemCode);
        $this->assertSame($code, $orders->get('order-id')->positions[0]->itemCode);
        $this->assertSame($code, $orders->list()->items[0]->positions[0]->itemCode);
    }

    /**
     * @dataProvider codes
     */
    public function testDirectSellAndBuyPreserveCode(?string $code): void
    {
        foreach ([false, true] as $buy) {
            $position = new PositionModel();
            $position->itemCode = $code;
            $details = $buy ? new DetailsBuyModel() : new DetailsSellModel();
            $details->actionType = 'PREPARE';
            $details->positions = [$position];
            $request = $buy ? new CreateBuy() : new CreateSell();
            $request->details = $details;
            $api = $this->createMock(ApiHttpClientInterface::class);
            $api->expects($this->once())->method('request')->willReturnCallback(
                function (string $method, string $action, array $query, string $body) use ($code, $buy): string {
                    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                    $this->assertSame('POST', $method);
                    $this->assertSame('operations', $action);
                    $this->assertSame($buy ? 'BUY' : 'SELL', $data['operationType']);
                    $this->assertSame($code, $data['details']['positions'][0]['itemCode']);
                    return '{}';
                }
            );
            (new Operations($api))->create($request);
        }
    }

    public static function codes(): iterable
    {
        foreach ([null, '0', '000123', 'AbC-123', ' 000123 ', str_repeat('я', 255), str_repeat('😀', 255)] as $code) {
            yield [$code];
        }
    }

    public function testOldResponseWithoutCodeAndOldPositionStillWork(): void
    {
        $position = new PositionModel();
        $position->name = 'Товар';
        $this->assertNull($position->itemCode);
        unset($position->itemCode);
        $request = new CreateRequest();
        $request->positions = [$position];
        $api = $this->createMock(ApiHttpClientInterface::class);
        $api->expects($this->once())->method('request')->willReturnCallback(
            function (string $method, string $action, array $query, string $body): string {
                $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                $this->assertArrayNotHasKey('itemCode', $data['positions'][0]);
                return '{"positions":[{"name":"Товар"}]}';
            }
        );
        $response = (new Orders($api))->create($request);
        $this->assertNull($response->positions[0]->itemCode);
        $this->assertSame('Товар', $response->positions[0]->name);
    }
}
