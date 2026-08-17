<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Generator;

use Siganushka\PaymentBundle\Model\PaymentInterface;

interface PaymentNumberGeneratorInterface
{
    public function generate(PaymentInterface $entity): string;
}
