<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Dto;

use Siganushka\GenericBundle\Dto\DateRangeDto;
use Siganushka\PaymentBundle\Enum\PaymentState;

class PaymentQueryDto
{
    public function __construct(
        public readonly ?string $number = null,
        public readonly ?string $gateway = null,
        public readonly ?string $dtype = null,
        public readonly ?PaymentState $state = null,
        public readonly ?DateRangeDto $created = null,
    ) {
    }
}
