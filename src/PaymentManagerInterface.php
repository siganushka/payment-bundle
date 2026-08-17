<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

use Siganushka\PaymentBundle\Model\PaymentInterface;
use Siganushka\PaymentBundle\Model\PaymentRefundInterface;

interface PaymentManagerInterface
{
    public function pay(PaymentInterface $payment): array;

    public function refund(PaymentInterface $payment, PaymentRefundInterface $refund): array;
}
