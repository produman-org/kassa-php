<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

abstract class AbstractCreateRequest implements RequestInterface
{
    use RequestTrait;

    /**
     * Potential values are 'X_REPORT', 'SHIFT_OPEN', 'SHIFT_CLOSE', 'KKT_INFO', 'CASH_IN', 'CASH_OUT', 'COPY_RECEIPT', 'PRINT_SLIP', 'SET_DATETIME', 'ACQUIRING_REPORT', 'SELL', 'SELL_RETURN', 'BUY', 'BUY_RETURN', 'CORRECTION_SELL', 'CORRECTION_BUY', 'CORRECTION_SELL_RETURN', 'CORRECTION_BUY_RETURN'.
     */
    protected string $operationType;

    public ?string $cashboxId = null;

    public ?string $createdById = null;

    public function getOperationType(): string
    {
        return $this->operationType;
    }
}
