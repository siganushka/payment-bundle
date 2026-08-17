<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Factory;

use Siganushka\PaymentBundle\Model\PaymentInterface;

interface PaymentFactoryInterface
{
    public function createPayment(string $type, int|string $identifier, string $gateway): PaymentInterface;

    public function supportsType(string $type): bool;
}
