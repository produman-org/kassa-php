<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Traits;

use PHPUnit\Framework\TestCase;
use ProdumanApi\Tests\Mock\Response\MockResponseWithResponseTrait;
use ProdumanApi\Tests\Mock\Response\Model\ArrayElement;

class ResponseTraitTest extends TestCase
{
    /**
     * @testWith
     * ["aa", 11, 11.11, false]
     * ["bb", 22, 22.22, true]
     * ["cc", 33, 33.33, false]
     * ["dd", 44, 44.44, true]
     * ["ee", 55, 55.55, false]
     */
    public function testCreateResponseObject(string $stringField, int $intField, float $floatField, bool $boolField): void
    {
        $dateTime = new \DateTime((new \DateTime())->format('c'));

        $object = new MockResponseWithResponseTrait();

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
            $arrayElement,
            clone $arrayElement,
            clone $arrayElement,
        ];

        $response = [
            'stringField' => $stringField,
            'intField' => $intField,
            'floatField' => $floatField,
            'boolField' => $boolField,
            'dateField' => $dateTime->format('c'),
            'arrayField' => [
                [
                    'stringField' => $stringField,
                    'intField' => $intField,
                    'floatField' => $floatField,
                    'boolField' => $boolField,
                    'dateField' => $dateTime->format('c'),
                ],
                [
                    'stringField' => $stringField,
                    'intField' => $intField,
                    'floatField' => $floatField,
                    'boolField' => $boolField,
                    'dateField' => $dateTime->format('c'),
                ],
                [
                    'stringField' => $stringField,
                    'intField' => $intField,
                    'floatField' => $floatField,
                    'boolField' => $boolField,
                    'dateField' => $dateTime->format('c'),
                ],
            ],
        ];

        $responseObject = MockResponseWithResponseTrait::createResponseObject($response);

        $this->assertEquals($object, $responseObject);
    }
}
