<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Siganushka\PaymentBundle\Dto\PaymentCreateDto;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentFailureEvent;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
use Siganushka\PaymentBundle\Exception\PaymentFailedException;
use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Siganushka\PaymentBundle\Factory\PaymentFactoryInterface;
use Siganushka\PaymentBundle\Form\PaymentRefundType;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PaymentRepository $paymentRepository)
    {
    }

    public function getCollection(PaginatorInterface $paginator, #[MapQueryString] PaymentQueryDto $dto): Response
    {
        $qb = $this->paymentRepository->createQueryBuilderByDto('t', $dto);
        $pagination = $paginator->paginate($qb);

        return $this->json($pagination, context: [
            AbstractNormalizer::GROUPS => ['payment.collection'],
        ]);
    }

    public function postCollection(PaymentGatewayRegistry $registry, PaymentFactoryInterface $factory, #[MapRequestPayload] PaymentCreateDto $dto): Response
    {
        try {
            $entity = $factory->createPayment($dto->type, $dto->identifier, $dto->gateway);
        } catch (\Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }

        try {
            $gateway = $registry->get($dto->gateway);
        } catch (UnsupportedGatewayException $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }

        if (!$gateway->supports($entity)) {
            throw new BadRequestHttpException(\sprintf('The payment unsupported gateway "%s".', $dto->gateway));
        }

        // Persist to generate number.
        $this->entityManager->persist($entity);

        try {
            $result = $gateway->pay($entity);
            if (PaymentState::Succeed === $entity->getState()) {
                $this->eventDispatcher->dispatch(new PaymentSuccessEvent($entity));
            }

            $this->entityManager->flush();

            return $this->json($result);
        } catch (\Throwable $th) {
            if ($th instanceof PaymentFailedException) {
                $entity->setState(PaymentState::Failed);
                $entity->setDetails($th->getDetails());
                $entity->setFailedReason($th->getMessage());
                $this->eventDispatcher->dispatch(new PaymentFailureEvent($entity));
                $this->entityManager->flush();
            }

            $error = $th->getMessage();
            $this->logger->error(__METHOD__, compact('error'));

            throw new BadRequestHttpException('Payment failed, please try again.', $th);
        }
    }

    public function getItem(string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        return $this->json($entity, context: [
            AbstractNormalizer::GROUPS => ['payment.item'],
        ]);
    }

    public function getRefunds(string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        return $this->json($entity->getRefunds(), context: [
            AbstractNormalizer::GROUPS => ['payment_refund.collection'],
        ]);
    }

    public function postRefunds(Request $request, PaymentGatewayRegistry $registry, string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        if (PaymentState::Succeed !== $entity->getState()) {
            throw new BadRequestHttpException('The payment is non-refundable.');
        }

        try {
            $gateway = $registry->get($entity->getGateway() ?? '');
        } catch (UnsupportedGatewayException $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }

        $refundCount = \count($entity->getRefunds());
        $refundNumber = \sprintf('%s%02d', $entity->getNumber(), ++$refundCount);

        $refund = new PaymentRefund();
        $refund->setPayment($entity);
        $refund->setNumber($refundNumber);

        $refundable = $refund->getRefundableAmount();
        if (null !== $refundable && $refundable <= 0) {
            throw new BadRequestHttpException('The payment has been fully refunded.');
        }

        $form = $this->createForm(PaymentRefundType::class, $refund);
        $form->submit($request->getPayload()->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Persist to generate number.
        $this->entityManager->persist($refund);

        try {
            $gateway->refund($entity, $refund);
            $entity->addRefund($refund);
            $this->entityManager->flush();

            return $this->json($refund, context: [
                AbstractNormalizer::GROUPS => ['payment_refund.item'],
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof PaymentFailedException) {
                $refund->setDetails($th->getDetails());
                $refund->setSuccessful(false);
                $refund->setFailedReason($th->getMessage());
                $this->entityManager->flush();
            }

            $error = $th->getMessage();
            $this->logger->error(__METHOD__, compact('error'));

            throw new BadRequestHttpException('Refund failed, please try again.', $th);
        }
    }
}
