<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock\Request\Model;

use ProdumanApi\Traits\RequestTrait;

class ArrayElement
{
    use RequestTrait;

    public ?string $stringField = null;

    public ?int $intField = null;

    public ?float $floatField = null;

    public ?bool $boolField = null;

    public ?\DateTimeInterface $dateField = null;
}
