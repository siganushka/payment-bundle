<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\PaymentResultInterface;

interface PaymentGatewayInterface
{
    public static function getName(): string;

    public function pay(Payment $payment, array $context = []): PaymentResultInterface;

    public function refund(Payment $payment, array $context = []): array;

    public function notify(Payment $payment, array $context = []): void;
}
