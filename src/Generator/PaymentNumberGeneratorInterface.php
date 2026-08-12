<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Generator;

use Siganushka\PaymentBundle\Entity\AbstractPayment;

interface PaymentNumberGeneratorInterface
{
    public function generate(AbstractPayment $entity): string;
}
