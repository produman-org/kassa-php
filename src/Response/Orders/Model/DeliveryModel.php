<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class DeliveryModel
{
    use ResponseTrait;

    public ?string $courierId = null;

    public ?string $address = null;

    public ?\DateTimeInterface $dateFrom = null;

    public ?\DateTimeInterface $dateTo = null;

    public ?string $status = null;

    public ?string $comment = null;

    public ?bool $prepaid = null;
}
