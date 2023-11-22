<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class SlipItemModel
{
    use RequestTrait;

    /**
     * Potential values are 'TEXT', 'QR', 'EAN_13', 'CODE_39', 'CODE_128', 'PDF_417'.
     */
    public ?string $type = null;

    public ?string $content = null;
}
