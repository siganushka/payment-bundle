<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Message\PaymentCancelMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class PaymentCancelMessageListener
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function __invoke(Payment $entity): void
    {
        $number = $entity->getNumber();
        if (!$number || PaymentState::Pending !== $entity->getState() || !$entity->getExpiredAt()) {
            return;
        }

        $message = new PaymentCancelMessage($number);
        $envelope = (new Envelope($message))
            ->with(DelayStamp::delayUntil($entity->getExpiredAt()))
        ;

        $this->messageBus->dispatch($envelope);
    }
}
