<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Message\PaymentCancelMessage;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class PaymentCancelMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function __invoke(PaymentCancelMessage $message): void
    {
        $this->entityManager->wrapInTransaction(function () use ($message) {
            $entity = $this->paymentRepository->findOneByNumberWithLock($message->getNumber());
            if ($entity && PaymentState::Pending === $entity->getState()) {
                $entity->setState(PaymentState::Cancelled);
            }
        });
    }
}
