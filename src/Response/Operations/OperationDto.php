<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Operations;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Response\Operations\Model\DetailsModel;
use ProdumanApi\Traits\ResponseTrait;

class OperationDto implements ResponseInterface
{
    use ResponseTrait;

    public ?string $id = null;

    public ?int $number = null;

    public ?\DateTimeInterface $createdAt = null;

    public ?string $operationType = null;

    public ?string $status = null;

    public ?string $message = null;

    public ?string $cashboxId = null;

    public ?string $createdById = null;

    public ?DetailsModel $details = null;
}
