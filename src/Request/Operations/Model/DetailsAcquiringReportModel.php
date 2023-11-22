<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsAcquiringReportModel
{
    use RequestTrait;

    /**
     * Potential values are 'SHORT', 'FULL'.
     */
    public ?string $acquiringReportType = null;
}
