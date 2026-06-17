<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Siganushka\PaymentBundle\Dto\PaymentCreateDto;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Exception\PaymentFailedException;
use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Siganushka\PaymentBundle\Factory\PaymentFactoryInterface;
use Siganushka\PaymentBundle\Form\PaymentRefundType;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;
use Siganushka\PaymentBundle\PaymentManagerInterface;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
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

    public function postCollection(
        NormalizerInterface $normalizer,
        PaymentGatewayRegistry $registry,
        PaymentFactoryInterface $factory,
        PaymentManagerInterface $paymentManager,
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

        // Persist to generate number.
        $this->entityManager->persist($entity);

        try {
            $result = $paymentManager->pay($entity);
        } catch (\Throwable $th) {
            $error = $th->getMessage();
            $this->logger->error(__METHOD__, compact('error'));

            throw new BadRequestHttpException($th instanceof PaymentFailedException ? $error : 'Payment failed, please try again.', $th);
        }

        $this->entityManager->flush();

        $data = $normalizer->normalize($entity, context: [
            AbstractNormalizer::GROUPS => ['payment.item'],
        ]);

        return $this->json($data + compact('result'));
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

    public function postRefunds(Request $request, PaymentManagerInterface $paymentManager, string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        $refundable = $entity->getRefundableAmount();
        if (null === $refundable || $refundable <= 0) {
            throw new BadRequestHttpException(null === $refundable ? 'The payment is non-refundable.' : 'The payment has been fully refunded.');
        }

        $refund = $paymentManager->createPaymentRefund($entity);

        $form = $this->createForm(PaymentRefundType::class, $refund);
        $form->submit($request->getPayload()->all());

        if (!$form->isValid()) {
            return $this->json($form, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $paymentManager->refund($entity, $refund);
        } catch (\Throwable $th) {
            $error = $th->getMessage();
            $this->logger->error(__METHOD__, compact('error'));

            throw new BadRequestHttpException($th instanceof PaymentFailedException ? $error : 'Payment failed, please try again.', $th);
        }

        $this->entityManager->flush();

        return $this->json($refund, context: [
            AbstractNormalizer::GROUPS => ['payment_refund.item'],
        ]);
    }
}
