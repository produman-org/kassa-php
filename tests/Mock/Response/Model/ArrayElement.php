<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock\Response\Model;

use ProdumanApi\Traits\ResponseTrait;

class ArrayElement
{
    use ResponseTrait;

    public ?string $stringField = null;

    public ?int $intField = null;

    public ?float $floatField = null;

    public ?bool $boolField = null;

    public ?\DateTimeInterface $dateField = null;
}
