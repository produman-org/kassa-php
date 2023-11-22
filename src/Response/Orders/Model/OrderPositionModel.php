<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class OrderPositionModel
{
    use ResponseTrait;

    public ?string $id = null;

    public ?string $name = null;

    public ?float $quantity = null;

    public ?float $price = null;

    public ?string $paymentVat = null;

    public ?string $paymentObject = null;

    public ?string $paymentMethod = null;

    public ?bool $excisable = null;

    /** @var string[] */
    public array $marks = [];

    public ?AgentSchemeModel $agentScheme = null;

    public ?string $group = null;
}
