<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsCorrectionModel extends DetailsSellModel
{
    use RequestTrait {
        RequestTrait::getRequestContent as getTraitRequestContent;
    }

    /**
     * Potential values are 'SELF', 'INSTRUCTION'.
     */
    public ?string $correctionType = null;

    public ?string $invalidReceiptFiscalSign = null;

    public ?\DateTimeInterface $foundationDocDate = null;

    public ?string $foundationDocNumber = null;

    public function getRequestContent(): array
    {
        $request = $this->getTraitRequestContent();

        self::dateTimeFieldFormat($request, 'foundationDocDate', 'Y-m-d');

        return $request;
    }
}
