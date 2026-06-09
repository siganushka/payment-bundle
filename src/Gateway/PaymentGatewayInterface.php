<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\Payment;

interface PaymentGatewayInterface extends \Stringable
{
    public static function getName(): string;

    public function pay(Payment $payment): array;

    public function refund(Payment $payment): array;

    public function supports(Payment $payment): bool;
}
