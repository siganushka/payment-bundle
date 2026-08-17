<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Event;

use Siganushka\PaymentBundle\Model\PaymentInterface;
use Siganushka\PaymentBundle\Model\PaymentRefundInterface;

class RefundEvent extends PaymentEvent
{
    public function __construct(
        PaymentInterface $payment,
        protected readonly PaymentRefundInterface $refund)
    {
        parent::__construct($payment);
    }

    public function getRefund(): PaymentRefundInterface
    {
        return $this->refund;
    }
}
