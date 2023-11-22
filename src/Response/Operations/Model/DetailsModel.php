<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Operations\Model;

use ProdumanApi\Traits\ResponseTrait;

class DetailsModel
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    public ?OperationOrderModel $order = null;

    /** @var PaymentSolutionModel[] */
    public array $paymentSolutions = [];

    /** @var FiscalAmountModel[] */
    public array $fiscalAmounts = [];

    public ?float $operationAmount = null;

    public ?string $actionType = null;

    public ?string $cashMovementCategoryId = null;

    public ?string $comment = null;

    public ?float $amount = null;

    public ?float $cashBalance = null;

    public ?string $fdNumber = null;

    public ?string $fdSign = null;

    public ?\DateTimeInterface $fdCreatedAt = null;

    public ?string $ofdLink = null;

    public ?string $rrn = null;

    public ?string $authorizationCode = null;

    public ?\DateTimeInterface $dateTimeKkm = null;

    public ?string $companyInn = null;

    public ?string $companyName = null;

    public ?string $regNumber = null;

    public ?string $serialNumber = null;

    public ?string $fnNumber = null;

    public ?string $ofdName = null;

    public ?string $ofdInn = null;

    public ?string $modelName = null;

    public ?string $installAddress = null;

    public ?string $installPlace = null;

    public ?string $shiftState = null;

    public ?string $firmwareVersion = null;

    public ?string $softVersion = null;

    public ?int $unsendFdCount = null;

    public ?\DateTimeInterface $unsendFdFrom = null;

    public ?string $ffdVersionKkm = null;

    public ?\DateTimeInterface $dateTimeSystem = null;

    public ?string $acquiringReportType = null;

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'paymentSolutions', PaymentSolutionModel::class);

        self::fillArrayOfObjects($object, $response, 'fiscalAmounts', FiscalAmountModel::class);

        return $object;
    }
}
