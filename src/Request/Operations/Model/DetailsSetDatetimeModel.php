<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsSetDatetimeModel
{
    use RequestTrait {
        RequestTrait::getRequestContent as getTraitRequestContent;
    }

    public ?\DateTimeInterface $dateTime = null;

    public function getRequestContent(): array
    {
        $request = $this->getTraitRequestContent();

        self::dateTimeFieldFormat($request, 'dateTime');

        return $request;
    }
}
