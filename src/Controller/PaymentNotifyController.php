<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentFailureEvent;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Siganushka\PaymentBundle\Result\RefundNotifyResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentNotifyController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentRepository $paymentRepository)
    {
    }

    public function notify(Request $request, PaymentGatewayRegistry $registry, string $gateway): Response
    {
        $this->logger->info('The payment notify has been triggered.', compact('gateway'));

        try {
            $gateway = $registry->get($gateway);
        } catch (UnsupportedGatewayException $th) {
            return new Response($th->getSafeMessage());
        }

        try {
            $result = $gateway->notify($request);

            $this->entityManager->wrapInTransaction($result instanceof RefundNotifyResult
                ? fn () => $this->handleRefund($result)
                : fn () => $this->handlePay($result));

            return $gateway->notifyResponse(true);
        } catch (\Throwable $th) {
            $error = $th->getMessage();
            $this->logger->error(__METHOD__, compact('error'));

            return $gateway->notifyResponse(false, $th->getMessage());
        }
    }

    private function handlePay(NotifyResult $result): void
    {
        $entity = $this->paymentRepository->findOneByNumberWithLock($result->getNumber())
            ?? throw new \RuntimeException('Payment not found.');

        if ($entity->getAmount() !== $result->getAmount()) {
            throw new \RuntimeException('Payment notify amount invalid.');
        }

        [$state, $event] = $result->isSuccessful()
            ? [PaymentState::Succeed, new PaymentSuccessEvent($entity)]
            : [PaymentState::Failed, new PaymentFailureEvent($entity)];

        if ($state === $entity->getState()) {
            return;
        }

        $entity->setDetails($result->getDetails());
        $entity->setState($state);

        $this->eventDispatcher->dispatch($event);
    }

    private function handleRefund(RefundNotifyResult $result): void
    {
        $number = $result->getNumber();
        $entity = $this->entityManager->getRepository(PaymentRefund::class)->findOneBy(compact('number'))
            ?? throw new \RuntimeException('Payment refund not found.');

        if ($entity->getAmount() !== $result->getAmount()) {
            throw new \RuntimeException('Payment refund notify amount invalid.');
        }

        if ($entity->isSuccessful()) {
            return;
        }

        $entity->setDetails($result->getDetails());
        $entity->setSuccessful($result->isSuccessful());
    }
}
