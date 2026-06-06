<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;
use Siganushka\PaymentBundle\NotifyResult;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
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
        $this->logger->info('Payment notify has been triggered.', compact('gateway'));

        try {
            $gateway = $registry->get($gateway);
        } catch (UnsupportedGatewayException) {
            return new Response(\sprintf('Payment gateway "%s" not found.', $gateway));
        }

        try {
            $result = $gateway->notify($request);
            $callback = fn () => $this->handleNotify($result);

            $this->entityManager->wrapInTransaction($callback);

            return $gateway->notifyResponse(true);
        } catch (\Throwable $th) {
            $this->logger->error('Payment notify error.', ['msg' => $th->getMessage()]);

            return $gateway->notifyResponse(false, 'Invalid Request.');
        }
    }

    private function handleNotify(NotifyResult $result): void
    {
        $entity = $this->paymentRepository->findOneByNumberWithLock($result->getPaymentIdentifier());
        if (!$entity) {
            throw new \RuntimeException('Payment not found.');
        }

        if ($entity->getAmount() !== $result->getAmount()) {
            throw new \RuntimeException('Payment notify amount invalid.');
        }

        if (PaymentState::Succeed === $entity->getState()) {
            return;
        }

        $entity->setDetails($result->getData());
        $entity->setState(PaymentState::Succeed);

        $event = new PaymentSuccessEvent($entity);
        $this->eventDispatcher->dispatch($event);
    }
}
