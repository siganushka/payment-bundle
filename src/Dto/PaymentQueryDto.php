<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Dto;

use Doctrine\Common\Collections\Expr\Comparison;
use Siganushka\GenericBundle\Attribute\QueryFilter;
use Siganushka\GenericBundle\Dto\DateRangeDto;
use Siganushka\PaymentBundle\Enum\PaymentState;

class PaymentQueryDto
{
    public function __construct(
        #[QueryFilter(expr: Comparison::CONTAINS)]
        public readonly ?string $number = null,
        #[QueryFilter]
        public readonly ?string $gateway = null,
        #[QueryFilter]
        public readonly ?PaymentState $state = null,
        #[QueryFilter(expr: ['startAt' => Comparison::GTE, 'endAt' => Comparison::LTE])]
        public readonly ?DateRangeDto $createdAt = null,
        public readonly ?string $dtype = null,
    ) {
    }
}
