<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Service;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ProdumanApi\Service\LoggerHelper;

final class LoggerHelperTest extends TestCase
{
    public function testRequestContextUsesOnlyTransportMetadata(): void
    {
        $options = ['timeout' => 5, 'max_redirects' => 0, 'headers' => ['X-APP-SECRET' => 'secret'], 'query' => ['token' => 'secret'], 'body' => '{"secret":"value"}'];
        self::assertSame(['timeout' => 5, 'max_redirects' => 0], LoggerHelper::prepareRequestOptionsContext($options));
        self::assertSame('secret', $options['headers']['X-APP-SECRET']);
    }

    public function testThrowableContextDoesNotIncludeMessageOrTrace(): void
    {
        $error = new \RuntimeException('secret', 123);
        self::assertSame(['exceptionClass' => \RuntimeException::class, 'code' => 123], LoggerHelper::prepareThrowableContext($error));
    }

    #[TestWith(['https://user:password@example.test/path?token=secret#fragment', 'https://example.test/path'])]
    #[TestWith(['http://127.0.0.1:42100/kassa', 'http://127.0.0.1:42100/kassa'])]
    #[TestWith(['not-a-url', '[invalid URL]'])]
    #[TestWith(['http://example.test:99999', '[invalid URL]'])]
    public function testUrlLoggingRemovesCredentialsAndQuery(string $url, string $expected): void
    {
        self::assertSame($expected, LoggerHelper::prepareUrl($url));
    }
}
