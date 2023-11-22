<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Employees;

use ProdumanApi\Traits\ResponseTrait;

class EmployeeDto
{
    use ResponseTrait;

    public ?string $id = null;

    public ?string $name = null;

    public ?string $inn = null;

    public ?string $status = null;

    public ?string $role = null;

    /** @var string[] */
    public array $cashboxIds = [];
}
