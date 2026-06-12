<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Siganushka\PaymentBundle\Dto\PaymentCreateDto;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentFailureEvent;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
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
    public function __construct(private readonly PaymentRepository $paymentRepository)
    {
    }

    public function getCollection(PaginatorInterface $paginator, #[MapQueryString] PaymentQueryDto $dto): Response
    {
        $queryBuilder = $this->paymentRepository->createQueryBuilderByDto('t', $dto);
        $pagination = $paginator->paginate($queryBuilder);

        return $this->json($pagination, context: [
            'groups' => ['payment.collection'],
        ]);
    }

    public function postCollection(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        PaymentGatewayRegistry $registry,
        PaymentFactoryInterface $factory,
        #[MapRequestPayload] PaymentCreateDto $dto): Response
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

        $entityManager->persist($entity);

        try {
            $result = $gateway->pay($entity);
            if ($result->isCompleted()) {
                $entity->setDetails($result->getDetails());
                $entity->setState(PaymentState::Succeed);
                $eventDispatcher->dispatch(new PaymentSuccessEvent($entity));
            }

            $entityManager->flush();
            if ($data = $result->getData()) {
                return $this->json($data);
            }

            return $this->json($entity, context: [
                AbstractNormalizer::GROUPS => ['payment.item'],
            ]);
        } catch (\Throwable $th) {
            $entity->setFailedReason($th->getMessage());
            $entity->setState(PaymentState::Failed);
            $eventDispatcher->dispatch(new PaymentFailureEvent($entity));
            $entityManager->flush();

            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }

    public function getItem(string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        return $this->json($entity, context: [
            'groups' => ['payment.item'],
        ]);
    }

    public function getRefunds(string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        return $this->json($entity->getRefunds(), context: [
            'groups' => ['payment_refund.collection'],
        ]);
    }

    public function postRefunds(Request $request, EntityManagerInterface $entityManager, PaymentGatewayRegistry $registry, string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        $gateway = $entity->getGateway();
        if (!$gateway || PaymentState::Succeed !== $entity->getState()) {
            throw new BadRequestHttpException('The payment is non-refundable.');
        }

        $refundCount = \count($entity->getRefunds());
        $refundNumber = \sprintf('%s%02d', $entity->getNumber(), ++$refundCount);

        $refund = new PaymentRefund();
        $refund->setNumber($refundNumber);
        $refund->setPayment($entity);

        if (!$refund->getRefundableAmount()) {
            throw new BadRequestHttpException('The payment has been fully refunded.');
        }

        $form = $this->createForm(PaymentRefundType::class, $refund);
        $form->submit($request->getPayload()->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $result = $registry->get($gateway)->refund($entity, $refund);
            if ($result->isCompleted()) {
                $refund->setDetails($result->getDetails());
                $refund->setSuccessful(true);
            }

            $entity->addRefund($refund);
            $entityManager->flush();

            return $this->json($refund, context: [
                'groups' => ['payment_refund.item'],
            ]);
        } catch (\Throwable $th) {
            $refund->setSuccessful(false);
            $refund->setFailedReason($th->getMessage());
            $entityManager->flush();

            throw new BadRequestHttpException($th->getMessage(), $th);
        }
    }
}
