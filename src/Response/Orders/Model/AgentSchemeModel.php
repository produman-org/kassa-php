<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class AgentSchemeModel
{
    use ResponseTrait;

    public ?string $agentSign = null;

    public ?PaymentAgentModel $paymentAgent = null;

    public ?ReceivePaymentsOperatorModel $receivePaymentsOperator = null;

    public ?TransferOperatorModel $transferOperator = null;

    public ?SupplierModel $supplier = null;
}
