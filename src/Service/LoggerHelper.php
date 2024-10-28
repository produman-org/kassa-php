<?php

declare(strict_types=1);

namespace ProdumanApi\Service;

class LoggerHelper
{
    public const REQUEST_LOG_FORMAT = '[Produman API Request]: %s URL: "%s"';

    public const RESPONSE_LOG_FORMAT = '[Produman API Response]: %s URL: "%s" ResponseStatusCode: "%s"';

    public const THROW_LOG_FORMAT = '[Produman API Throw]: Code: "%s", Message: "%s"';

    public static function prepareRequestOptionsContext(array $options): array
    {
        array_walk_recursive($options, function (&$item, $key) {
            if (in_array($key, ['X-APP-SECRET', 'X-CLIENT-TOKEN'])) {
                $item = '*****';
            }
        });

        return $options;
    }

    public static function prepareThrowableContext(\Throwable $throwable): array
    {
        return [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'exceptionClass' => get_class($throwable),
            'trace' => $throwable->getTraceAsString(),
        ];
    }
}
