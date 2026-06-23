<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Tests\Fixtures;

use Siganushka\PaymentBundle\Entity\Payment;

class FooPayment extends Payment
{
    public function __construct(string $gateway, int $amount)
    {
        $this->amount = $amount;

        parent::__construct($gateway);
    }
}
