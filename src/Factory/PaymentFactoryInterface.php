<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Factory;

use Siganushka\PaymentBundle\Entity\AbstractPayment;

interface PaymentFactoryInterface
{
    public function createPayment(string $type, int|string $identifier, string $gateway): AbstractPayment;

    public function supportsType(string $type): bool;
}
