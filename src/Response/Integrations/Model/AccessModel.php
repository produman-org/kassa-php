<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Integrations\Model;

use ProdumanApi\Traits\ResponseTrait;

class AccessModel
{
    use ResponseTrait;

    public ?string $entity = null;

    public ?string $scope = null;

    /** @var string[] */
    public array $permissions = [];
}
