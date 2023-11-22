<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class DeliveryModel
{
    use RequestTrait {
        RequestTrait::getRequestContent as getTraitRequestContent;
    }

    public ?string $courierId = null;

    public ?string $address = null;

    public ?\DateTimeInterface $dateFrom = null;

    public ?\DateTimeInterface $dateTo = null;

    /**
     * Potential values are 'NEW', 'IN_PROGRESS', 'FINISHED', 'MOVED', 'CANCELED'.
     */
    public ?string $status = null;

    public ?string $comment = null;

    public ?bool $prepaid = null;

    public function getRequestContent(): array
    {
        $request = $this->getTraitRequestContent();

        self::dateTimeFieldFormat($request, 'dateFrom', 'Y-m-d H:i');

        self::dateTimeFieldFormat($request, 'dateTo', 'Y-m-d H:i');

        return $request;
    }
}
