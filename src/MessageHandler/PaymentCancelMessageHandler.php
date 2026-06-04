<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\MessageHandler;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Message\PaymentCancelMessage;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

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
        $this->entityManager->beginTransaction();

        try {
            $queryBuilder = $this->paymentRepository->createQueryBuilder('p')
                ->where('p.number = :number')
                ->setParameter('number', $message->getNumber())
                ->setMaxResults(1)
            ;

            $query = $queryBuilder->getQuery();
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

            $entity = $query->getOneOrNullResult();
            if (!$entity instanceof Payment) {
                throw new UnrecoverableMessageHandlingException('Payment not found.');
            }

            $entity->setState(PaymentState::Cancelled);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            $connection = $this->entityManager->getConnection();
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            if (!$exception instanceof UnrecoverableMessageHandlingException) {
                throw $exception;
            }
        }
    }
}
