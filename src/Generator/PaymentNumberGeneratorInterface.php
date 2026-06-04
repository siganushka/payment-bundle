<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Generator;

use Siganushka\PaymentBundle\Entity\Payment;

interface PaymentNumberGeneratorInterface
{
    public function generate(Payment $entity): string;
}
