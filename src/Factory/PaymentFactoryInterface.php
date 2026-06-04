<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Factory;

use Siganushka\PaymentBundle\Entity\Payment;

interface PaymentFactoryInterface
{
    public function createPayment(string $type, int|string $identifier, string $gateway): Payment;

    public function supportsType(string $type): bool;
}
