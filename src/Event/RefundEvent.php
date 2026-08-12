<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Event;

use Siganushka\PaymentBundle\Entity\AbstractPayment;
use Siganushka\PaymentBundle\Entity\AbstractPaymentRefund;

class RefundEvent extends PaymentEvent
{
    public function __construct(
        AbstractPayment $payment,
        protected readonly AbstractPaymentRefund $refund)
    {
        parent::__construct($payment);
    }

    public function getRefund(): AbstractPaymentRefund
    {
        return $this->refund;
    }
}
