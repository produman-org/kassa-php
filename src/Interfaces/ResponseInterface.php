<?php

declare(strict_types=1);

namespace ProdumanApi\Interfaces;

interface ResponseInterface
{
    public static function createResponseObject(array $response): ResponseInterface;
}
