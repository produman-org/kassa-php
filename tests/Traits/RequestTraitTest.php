<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Traits;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ProdumanApi\Tests\Mock\Request\MockRequestWithRequestTrait;
use ProdumanApi\Tests\Mock\Request\Model\ArrayElement;

class RequestTraitTest extends TestCase
{
    #[TestWith(['aa', 11, 11.11, false])]
    #[TestWith(['bb', 22, 22.22, true])]
    #[TestWith(['cc', 33, 33.33, false])]
    #[TestWith(['dd', 44, 44.44, true])]
    #[TestWith(['ee', 55, 55.55, false])]
    public function testGetRequestContent(string $stringField, int $intField, float $floatField, bool $boolField): void
    {
        $dateTime = new \DateTime();

        $object = new MockRequestWithRequestTrait();

        $object->stringField = $stringField;
        $object->intField = $intField;
        $object->floatField = $floatField;
        $object->boolField = $boolField;
        $object->dateField = $dateTime;

        $arrayElement = new ArrayElement();

        $arrayElement->stringField = $stringField;
        $arrayElement->intField = $intField;
        $arrayElement->floatField = $floatField;
        $arrayElement->boolField = $boolField;
        $arrayElement->dateField = $dateTime;

        $object->arrayField = [
            $stringField,
            $intField,
            $floatField,
            $boolField,
            $dateTime,
            $arrayElement,
        ];

        $this->assertEquals([
            'stringField' => $stringField,
            'intField' => $intField,
            'floatField' => $floatField,
            'boolField' => $boolField,
            'dateField' => $dateTime->format(MockRequestWithRequestTrait::DATE_FIELD_FORMAT),
            'arrayField' => [
                $stringField,
                $intField,
                $floatField,
                $boolField,
                $dateTime,
                [
                    'stringField' => $stringField,
                    'intField' => $intField,
                    'floatField' => $floatField,
                    'boolField' => $boolField,
                    'dateField' => $dateTime,
                ],
            ],
        ], $object->getRequestContent());
    }

    public function testDateTimeFieldFormat(): void
    {
        $dateTime = new \DateTime();

        $request = ['dateField' => $dateTime];
        MockRequestWithRequestTrait::dateTimeFieldFormat($request, 'dateField');
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $request['dateField']);

        $request = ['dateField' => $dateTime];
        MockRequestWithRequestTrait::dateTimeFieldFormat($request, 'dateField', 'Y-m-d');
        $this->assertEquals($dateTime->format('Y-m-d'), $request['dateField']);

        $request = ['dateField' => 123];
        $requestAfter = $request;
        MockRequestWithRequestTrait::dateTimeFieldFormat($request, 'dateField');
        $this->assertEquals($requestAfter, $request);

        $request = ['dateField' => $dateTime];
        $requestAfter = $request;
        MockRequestWithRequestTrait::dateTimeFieldFormat($request, 'unknownField');
        $this->assertEquals($requestAfter, $request);
    }
}
