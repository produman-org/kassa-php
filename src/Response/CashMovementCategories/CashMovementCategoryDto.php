<?php

declare(strict_types=1);

namespace ProdumanApi\Response\CashMovementCategories;

use ProdumanApi\Traits\ResponseTrait;

class CashMovementCategoryDto
{
    use ResponseTrait;

    public ?string $id = null;

    public ?string $name = null;

    /** @var string[] */
    public array $types = [];
}
