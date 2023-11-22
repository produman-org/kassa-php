<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class ListRequest implements RequestInterface
{
    use RequestTrait {
        RequestTrait::getRequestContent as getTraitRequestContent;
    }

    public ?int $limit = null;

    public ?string $cursor = null;

    /**
     * Potential values are 'DRAFT', 'IN_PROGRESS', 'COMPLETE', 'FAIL'.
     */
    public ?string $status = null;

    /**
     * Potential values are 'X_REPORT', 'SHIFT_OPEN', 'SHIFT_CLOSE', 'KKT_INFO', 'SELL', 'CASH_IN', 'CASH_OUT', 'SELL_RETURN', 'COPY_RECEIPT', 'PRINT_SLIP', 'CORRECTION_SELL', 'CORRECTION_BUY', 'CORRECTION_SELL_RETURN', 'CORRECTION_BUY_RETURN', 'SET_DATETIME', 'ACQUIRING_REPORT', 'BUY', 'BUY_RETURN'.
     */
    public ?string $operationType = null;

    public ?string $cashboxId = null;

    public ?string $createdById = null;

    public ?string $cashMovementCategoryId = null;

    public ?\DateTimeInterface $createdAtFrom = null;

    public ?\DateTimeInterface $createdAtTo = null;

    public function getRequestContent(): array
    {
        $request = $this->getTraitRequestContent();

        self::dateTimeFieldFormat($request, 'createdAtFrom');

        self::dateTimeFieldFormat($request, 'createdAtTo');

        return $request;
    }
}
