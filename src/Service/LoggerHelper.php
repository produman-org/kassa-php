<?php

declare(strict_types=1);

namespace ProdumanApi\Service;

class LoggerHelper
{
    public const REQUEST_LOG_FORMAT = '[Produman API Request]: %s URL: "%s"';
    public const RESPONSE_LOG_FORMAT = '[Produman API Response]: %s URL: "%s" ResponseStatusCode: "%s"';
    public const THROW_LOG_FORMAT = '[Produman API Throw]: Code: "%s", Message: "%s"';

    public static function prepareUrl(string $url): string
    {
        $parts = parse_url($url);
        if (false === $parts || !isset($parts['scheme'], $parts['host'])) {
            return '[invalid URL]';
        }

        return $parts['scheme'] . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . ($parts['path'] ?? '');
    }

    public static function prepareRequestOptionsContext(array $options): array
    {
        return array_intersect_key($options, ['timeout' => true, 'max_redirects' => true]);
    }

    public static function prepareThrowableContext(\Throwable $throwable): array
    {
        return ['exceptionClass' => $throwable::class, 'code' => $throwable->getCode()];
    }
}
