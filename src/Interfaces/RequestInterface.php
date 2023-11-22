<?php

declare(strict_types=1);

namespace ProdumanApi\Interfaces;

interface RequestInterface
{
    public function getRequestContent(): array;
}
