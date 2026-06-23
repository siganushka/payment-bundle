<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Entity\PaymentRefund;

interface PaymentManagerInterface
{
    public function pay(Payment $payment): array;

    public function refund(Payment $payment, PaymentRefund $refund): array;
}
