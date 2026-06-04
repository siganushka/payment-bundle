<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Tests\Fixtures\Bar;
use Siganushka\PaymentBundle\Tests\Fixtures\BarPayment;
use Siganushka\PaymentBundle\Tests\Fixtures\FooPayment;

class PaymentTest extends TestCase
{
    public function testAll(): void
    {
        $payment1 = new FooPayment(1024);
        static::assertSame(1024, $payment1->getAmount());
        static::assertNull($payment1->getNumber());
        static::assertNull($payment1->getGateway());
        static::assertNull($payment1->getDetails());
        static::assertSame(PaymentState::Pending, $payment1->getState());

        $payment2 = new BarPayment();
        $payment2->addBar(new Bar(1));
        $payment2->addBar(new Bar(2));
        $payment2->addBar(new Bar(3));
        $payment2->setState(PaymentState::Succeed);

        static::assertSame(6, $payment2->getAmount());
        static::assertNull($payment2->getNumber());
        static::assertNull($payment2->getGateway());
        static::assertNull($payment2->getDetails());
        static::assertSame(PaymentState::Succeed, $payment2->getState());
    }
}
