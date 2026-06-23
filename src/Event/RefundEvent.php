<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Event;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Entity\PaymentRefund;

class RefundEvent extends PaymentEvent
{
    public function __construct(
        Payment $payment,
        protected readonly PaymentRefund $refund)
    {
        parent::__construct($payment);
    }

    public function getRefund(): PaymentRefund
    {
        return $this->refund;
    }
}
