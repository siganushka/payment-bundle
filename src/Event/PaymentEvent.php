<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Event;

use Siganushka\PaymentBundle\Entity\Payment;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentEvent extends Event
{
    public function __construct(protected readonly Payment $payment)
    {
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
