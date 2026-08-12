<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

use Siganushka\PaymentBundle\Entity\AbstractPayment;
use Siganushka\PaymentBundle\Entity\AbstractPaymentRefund;

interface PaymentManagerInterface
{
    public function pay(AbstractPayment $payment): array;

    public function refund(AbstractPayment $payment, AbstractPaymentRefund $refund): array;
}
