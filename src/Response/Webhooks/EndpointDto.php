<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Webhooks;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Traits\ResponseTrait;

class EndpointDto implements ResponseInterface
{
    use ResponseTrait;

    public ?string $id = null;

    public ?\DateTimeInterface $createdAt = null;

    public ?string $status = null;

    public ?string $url = null;

    public ?string $authType = null;

    /** @var string[] */
    public array $events = [];
}
