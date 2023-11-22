<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Counterparties;

use ProdumanApi\Response\Counterparties\Model\AccountModel;
use ProdumanApi\Traits\ResponseTrait;

class CounterpartyDto
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    public ?string $id = null;

    public ?string $name = null;

    public ?string $phone = null;

    public ?string $inn = null;

    public ?string $kpp = null;

    /** @var AccountModel[] */
    public array $accounts = [];

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'accounts', AccountModel::class);

        return $object;
    }
}
