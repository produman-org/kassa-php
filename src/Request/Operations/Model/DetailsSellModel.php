<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Request\Orders\Model\PositionModel;
use ProdumanApi\Traits\RequestTrait;

class DetailsSellModel
{
    use RequestTrait;

    public ?string $orderId = null;

    /**
     * Potential values are 'PREPARE', 'EXECUTE'.
     */
    public ?string $actionType = null;

    /**
     * Potential values are 'OSN', 'USN_INCOME', 'USN_INCOME_OUTCOME', 'ESN', 'PATENT'.
     */
    public ?string $taxationSystem = null;

    public ?ClientModel $client = null;

    public ?string $receiptContact = null;

    public ?bool $print = null;

    public ?string $settlementPlace = null;

    public ?string $documentNumber = null;

    /** @var PaymentSolutionModel[]|null */
    public ?array $paymentSolutions = null;

    /** @var PositionModel[]|null */
    public ?array $positions = null;
}
