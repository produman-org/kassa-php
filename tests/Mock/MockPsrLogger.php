<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock;

use Psr\Log\AbstractLogger;

class MockPsrLogger extends AbstractLogger
{
    private array $logs = [];

    public function log($level, $message, array $context = [])
    {
        $this->logs[] = [
            $level,
            $message,
            $context,
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
