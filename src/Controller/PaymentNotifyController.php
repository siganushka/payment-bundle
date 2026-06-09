<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentFailureEvent;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Siganushka\PaymentBundle\Gateway\NotifiableGatewayInterface;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Siganushka\PaymentBundle\Result\PayNotifyResult;
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
        $this->logger->info('The gateway notify has been triggered.', compact('gateway'));

        try {
            $gateway = $registry->get($gateway);
        } catch (UnsupportedGatewayException $th) {
            return new Response($th->getSafeMessage());
        }

        if (!$gateway instanceof NotifiableGatewayInterface) {
            return new Response(\sprintf('The gateway "%s" is not supported notify.', $gateway));
        }

        try {
            $result = $gateway->notify($request);
            $callback = match (true) {
                $result instanceof PayNotifyResult => fn () => $this->handlePay($result),
                $result instanceof RefundNotifyResult => fn () => $this->handleRefund($result),
                default => throw new \InvalidArgumentException('Unexpected value.'),
            };

            $this->entityManager->wrapInTransaction($callback);

            return $gateway->notifyResponse(true);
        } catch (\Throwable $th) {
            $this->logger->error('The gateway notify error.', ['msg' => $th->getMessage()]);

            return $gateway->notifyResponse(false, $th->getMessage());
        }
    }

    private function handlePay(PayNotifyResult $result): void
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
        $this->logger->debug(__METHOD__, get_object_vars($result));
    }
}
