<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsAcquiringReportModel;

class CreateAcquiringReport extends AbstractCreateRequest
{
    protected string $operationType = 'ACQUIRING_REPORT';

    public ?DetailsAcquiringReportModel $details = null;
}
