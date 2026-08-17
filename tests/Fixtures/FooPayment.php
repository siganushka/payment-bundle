<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Tests\Fixtures;

use Siganushka\PaymentBundle\Entity\AbstractPayment;

class FooPayment extends AbstractPayment
{
    public function __construct(string $gateway, int $amount)
    {
        parent::__construct($gateway);

        $this->amount = $amount;
    }
}
