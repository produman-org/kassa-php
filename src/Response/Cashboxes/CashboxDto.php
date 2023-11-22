<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Cashboxes;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Response\Cashboxes\Model\AvailableOperationModel;
use ProdumanApi\Response\Cashboxes\Model\AvailablePaymentSolutionModel;
use ProdumanApi\Traits\ResponseTrait;

class CashboxDto implements ResponseInterface
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    public ?string $id = null;

    public ?string $name = null;

    public ?string $driver = null;

    public ?string $status = null;

    public ?string $serialNumber = null;

    public ?string $regNumber = null;

    public ?string $fnNumber = null;

    public ?string $ofdInn = null;

    public ?string $ffdVersionKkm = null;

    public ?int $lineLength = null;

    public ?float $cashBalance = null;

    /** @var string[] */
    public array $availableTaxationSystems = [];

    /** @var string[] */
    public array $availablePaymentMethods = [];

    /** @var string[] */
    public array $availablePaymentObjects = [];

    /** @var AvailableOperationModel[] */
    public array $availableOperations = [];

    /** @var AvailablePaymentSolutionModel[] */
    public array $availablePaymentSolutions = [];

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'availableOperations', AvailableOperationModel::class);

        self::fillArrayOfObjects($object, $response, 'availablePaymentSolutions', AvailablePaymentSolutionModel::class);

        return $object;
    }
}
