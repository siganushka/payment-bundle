<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Tests\Fixtures\Bar;
use Siganushka\PaymentBundle\Tests\Fixtures\BarPayment;
use Siganushka\PaymentBundle\Tests\Fixtures\FooPayment;
use Siganushka\PaymentBundle\Tests\Fixtures\TestPaymentRefund;

class PaymentTest extends TestCase
{
    public function testAll(): void
    {
        $payment1 = new FooPayment('x', 100);
        static::assertSame('x', $payment1->getGateway());
        static::assertSame(100, $payment1->getAmount());
        static::assertNull($payment1->getNumber());
        static::assertNull($payment1->getDetails());
        static::assertSame(PaymentState::Pending, $payment1->getState());
        static::assertSame(0, $payment1->getRefundedAmount());
        static::assertSame(0, $payment1->getRefundableAmount());

        $payment1->setState(PaymentState::Succeed);
        static::assertSame(100, $payment1->getRefundableAmount());

        $refund1 = new TestPaymentRefund($payment1);
        $refund1->setAmount(10);
        $refund1->setSuccessful(true);

        $refund2 = new TestPaymentRefund($payment1);
        $refund2->setAmount(20);
        $refund2->setSuccessful(true);

        $refund3 = new TestPaymentRefund($payment1);
        $refund3->setAmount(30);

        $payment1->addRefund($refund1);
        $payment1->addRefund($refund2);
        $payment1->addRefund($refund3);
        static::assertSame(30, $payment1->getRefundedAmount());
        static::assertSame(70, $payment1->getRefundableAmount());

        $refund3->setSuccessful(true);
        static::assertSame(60, $payment1->getRefundedAmount());
        static::assertSame(40, $payment1->getRefundableAmount());

        $payment2 = new BarPayment('y');
        $payment2->addBar(new Bar(1));
        $payment2->addBar(new Bar(2));
        $payment2->addBar(new Bar(3));
        $payment2->setState(PaymentState::Succeed);

        static::assertSame('y', $payment2->getGateway());
        static::assertSame(6, $payment2->getAmount());
        static::assertNull($payment2->getNumber());
        static::assertNull($payment2->getDetails());
        static::assertSame(PaymentState::Succeed, $payment2->getState());
    }
}
