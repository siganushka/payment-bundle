<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Event;

use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentEvent extends Event
{
    public function __construct(protected readonly PaymentInterface $payment)
    {
    }

    public function getPayment(): PaymentInterface
    {
        return $this->payment;
    }
}
