<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class PositionModel
{
    use RequestTrait;

    public ?string $name = null;

    public ?float $quantity = null;

    public ?float $price = null;

    /**
     * Potential values are 'WITHOUT', '0', '10', '10_110', '20', '20_120', '22', '22122'
     */
    public ?string $paymentVat = null;

    /**
     * Potential values are 'PRODUCT', 'JOB', 'SERVICE', 'GAMBLING_BET', 'GAMBLING_PRIZE', 'LOTTERY_TICKET', 'LOTTERY_PRIZE', 'INTELLECTUAL_ACTIVITY', 'PAYMENT', 'AGENT_COMMISSION', 'PAYOUT', 'ANOTHER', 'PROPRIETARY_LAW', 'NON_OPERATING_INCOME', 'OTHER_CONTRIBUTIONS', 'MERCHANT_TAX', 'RESORT_FEE', 'PLEDGE', 'CONSUMPTION', 'CONTRIBUTIONS_OPS_IP', 'CONTRIBUTIONS_OPS', 'CONTRIBUTIONS_OMS_IP', 'CONTRIBUTIONS_OMS', 'CONTRIBUTIONS_OSS', 'CASINO_PAYMENT', 'MONEY_PAYMENT'.
     */
    public ?string $paymentObject = null;

    /**
     * Potential values are 'FULL_PREPAYMENT', 'PREPAYMENT', 'ADVANCE', 'FULL_PAYMENT', 'PARTIAL_PAYMENT', 'CREDIT', 'CREDIT_PAYMENT'.
     */
    public ?string $paymentMethod = null;

    public ?bool $excisable = null;

    /** @var string[]|null */
    public ?array $marks = null;

    public ?AgentSchemeModel $agentScheme = null;
}
