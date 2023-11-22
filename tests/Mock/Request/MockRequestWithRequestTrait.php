<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock\Request;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class MockRequestWithRequestTrait implements RequestInterface
{
    use RequestTrait {
        RequestTrait::getRequestContent as getTraitRequestContent;
    }
    public const DATE_FIELD_FORMAT = 'Y--m--d__H::i::s';

    public ?string $stringField = null;

    public ?int $intField = null;

    public ?float $floatField = null;

    public ?bool $boolField = null;

    public ?\DateTimeInterface $dateField = null;

    public ?array $arrayField = null;

    public function getRequestContent(): array
    {
        $request = $this->getTraitRequestContent();

        self::dateTimeFieldFormat($request, 'dateField', self::DATE_FIELD_FORMAT);

        return $request;
    }
}
