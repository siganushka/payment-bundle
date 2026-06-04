<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Dto;

use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentCreateDto
{
    public function __construct(
        #[NotBlank]
        public readonly string $type,
        #[NotBlank]
        public readonly string|int $identifier,
        #[NotBlank]
        public readonly string $gateway,
    ) {
    }
}
