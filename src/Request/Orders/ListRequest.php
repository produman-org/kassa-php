<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class ListRequest implements RequestInterface
{
    use RequestTrait {
        RequestTrait::getRequestContent as getTraitRequestContent;
    }

    public ?int $limit = null;

    public ?string $cursor = null;

    public ?string $id = null;

    public ?\DateTimeInterface $createdAtFrom = null;

    public ?\DateTimeInterface $createdAtTo = null;

    public ?float $totalAmountFrom = null;

    public ?float $totalAmountTo = null;

    public function getRequestContent(): array
    {
        $request = $this->getTraitRequestContent();

        self::dateTimeFieldFormat($request, 'createdAtFrom');

        self::dateTimeFieldFormat($request, 'createdAtTo');

        return $request;
    }
}
