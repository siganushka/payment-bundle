<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentFailureEvent;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
use Siganushka\PaymentBundle\Exception\PaymentFailedException;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;

class PaymentManager implements PaymentManagerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PaymentGatewayRegistry $paymentRegistry)
    {
    }

    public function pay(Payment $payment): array
    {
        $gateway = $this->paymentRegistry->get($payment->getGateway() ?? '');

        try {
            // Persist to generate number.
            $this->entityManager->persist($payment);

            $result = $gateway->pay($payment);
            if (PaymentState::Succeed === $payment->getState()) {
                $this->eventDispatcher->dispatch(new PaymentSuccessEvent($payment));
            }

            $this->entityManager->flush();

            return $result;
        } catch (PaymentFailedException $th) {
            $payment->setState(PaymentState::Failed);
            $payment->setDetails($th->getDetails());
            $payment->setFailedReason($th->getMessage());
            $this->eventDispatcher->dispatch(new PaymentFailureEvent($payment));
            $this->entityManager->flush();

            throw $th;
        }
    }

    public function refund(Payment $payment, PaymentRefund $refund): array
    {
        $gateway = $this->paymentRegistry->get($payment->getGateway() ?? '');

        try {
            // Persist to generate number.
            $this->entityManager->persist($payment);

            $result = $gateway->refund($payment, $refund);

            $payment->addRefund($refund);
            $this->entityManager->flush();

            return $result;
        } catch (PaymentFailedException $th) {
            $refund->setDetails($th->getDetails());
            $refund->setSuccessful(false);
            $refund->setFailedReason($th->getMessage());
            $this->entityManager->flush();

            throw $th;
        }
    }

    public function createPaymentRefund(Payment $payment): PaymentRefund
    {
        $refundCount = \count($payment->getRefunds());
        $refundNumber = \sprintf('%s%02d', $payment->getNumber(), ++$refundCount);

        $refund = new PaymentRefund();
        $refund->setPayment($payment);
        $refund->setNumber($refundNumber);

        return $refund;
    }
}
