<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Siganushka\PaymentBundle\Dto\PaymentCreateDto;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentSuccessEvent;
use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Siganushka\PaymentBundle\Factory\PaymentFactoryInterface;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;
use Siganushka\PaymentBundle\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    public function postCollection(EntityManagerInterface $entityManager, PaymentGatewayRegistry $registry, PaymentFactoryInterface $factory, #[MapRequestPayload] PaymentCreateDto $dto): Response
    {
        try {
            $registry->get($dto->gateway);
        } catch (\Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }

        try {
            $entity = $factory->createPayment($dto->type, $dto->identifier, $dto->gateway);
        } catch (\Throwable $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return $this->json($entity, context: [
            'groups' => ['payment.item'],
        ]);
    }

    public function getItem(string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        return $this->json($entity, context: [
            'groups' => ['payment.item'],
        ]);
    }

    public function getItemPay(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, PaymentGatewayRegistry $registry, string $number): Response
    {
        $entity = $this->paymentRepository->findOneByNumber($number)
            ?? throw $this->createNotFoundException();

        $state = $entity->getState();
        if (PaymentState::Pending !== $state) {
            throw new \RuntimeException(\sprintf('The payment "%s" has been %s.', $number, $state->value));
        }

        $gateway = $entity->getGateway()
            ?? throw new BadRequestHttpException('Payment gateway error.');

        try {
            $result = $registry->get($gateway)->pay($entity);
        } catch (UnsupportedGatewayException $th) {
            throw new BadRequestHttpException($th->getMessage(), $th);
        } catch (\Throwable $th) {
            throw new BadRequestHttpException('Payment failed, please try again later.', $th);
        }

        if ($result->isSuccessful()) {
            $entityManager->beginTransaction();

            $entity->setState(PaymentState::Succeed);
            $eventDispatcher->dispatch(new PaymentSuccessEvent($entity));

            $entityManager->flush();
            $entityManager->commit();
        }

        return $this->json($result);
    }
}
