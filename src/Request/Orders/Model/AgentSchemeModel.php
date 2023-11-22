<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class AgentSchemeModel
{
    use RequestTrait;

    /**
     * Potential values are 'ANOTHER_AGENT', 'BANK_PAYING_AGENT', 'BANK_PAYING_SUBAGENT', '10PAYING_AGENT', 'PAYING_SUBAGENT', 'ATTORNEY', 'COMMISSION_AGENT'.
     */
    public ?string $agentSign = null;

    public ?PaymentAgentModel $paymentAgent = null;

    public ?ReceivePaymentsOperatorModel $receivePaymentsOperator = null;

    public ?TransferOperatorModel $transferOperator = null;

    public ?SupplierModel $supplier = null;
}
