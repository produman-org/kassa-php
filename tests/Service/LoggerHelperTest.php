<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Service;

use PHPUnit\Framework\TestCase;
use ProdumanApi\Service\LoggerHelper;
use ProdumanApi\Tests\ReflectionHelper;

final class LoggerHelperTest extends TestCase
{
    public function testConstants(): void
    {
        $constants = ReflectionHelper::getConstants(LoggerHelper::class);

        $this->assertContains('REQUEST_LOG_FORMAT', array_keys($constants));
        $this->assertContains('RESPONSE_LOG_FORMAT', array_keys($constants));
        $this->assertContains('THROW_LOG_FORMAT', array_keys($constants));

        $this->assertEquals('[Produman API Request]: %s URL: "%s"', $constants['REQUEST_LOG_FORMAT']);
        $this->assertEquals('[Produman API Response]: %s URL: "%s" ResponseStatusCode: "%s"', $constants['RESPONSE_LOG_FORMAT']);
        $this->assertEquals('[Produman API Throw]: Code: "%s", Message: "%s"', $constants['THROW_LOG_FORMAT']);
    }

    /**
     * @testWith
     * [{"a": "aaa", "X-APP-SECRET": "aaa", "aa": {"X-CLIENT-TOKEN": "aaa", "aa": "aaa"}}]
     * [{"X-APP-SECRET": "bbb", "b": "bbb", "bb": {"X-CLIENT-TOKEN": "bbb", "bb": "bbb"}}]
     * [{"cc": {"X-CLIENT-TOKEN": "ccc", "cc": "ccc"}, "c": "ccc", "X-APP-SECRET": "ccc"}]
     */
    public function testPrepareRequestOptionsContext(array $options)
    {
        $expectedOptions = $options;

        array_walk_recursive($expectedOptions, function (&$item, $key) {
            if (in_array($key, ['X-APP-SECRET', 'X-CLIENT-TOKEN'])) {
                $item = '*****';
            }
        });

        $this->assertEquals($expectedOptions, LoggerHelper::prepareRequestOptionsContext($options));
    }

    /**
     * @testWith
     * ["aaa", 1]
     * ["bbb", 2]
     * ["ccc", 3]
     */
    public function testPrepareThrowableContext(string $message, int $code)
    {
        try {
            throw new \Exception($message, $code);
        } catch (\Throwable $throwable) {
            $this->assertEquals([
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'exceptionClass' => get_class($throwable),
                'trace' => $throwable->getTraceAsString(),
            ], LoggerHelper::prepareThrowableContext($throwable));
        }
    }
}
