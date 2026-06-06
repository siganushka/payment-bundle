<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Siganushka\PaymentBundle\Dto\PaymentCreateDto;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Event\PaymentFailureEvent;
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

    public function postCollection(
        EventDispatcherInterface $eventDispatcher,
        EntityManagerInterface $entityManager,
        PaymentGatewayRegistry $registry,
        PaymentFactoryInterface $factory,
        #[MapRequestPayload]
        PaymentCreateDto $dto): Response
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
        $entityManager->flush();

        try {
            $result = $gateway->pay($entity);
            if ($result->isSuccessful()) {
                $entity->setDetails($result->getData());
                $entity->setState(PaymentState::Succeed);
                $eventDispatcher->dispatch(new PaymentSuccessEvent($entity));
                $entityManager->flush();
            }

            return $this->json($result);
        } catch (\Throwable $th) {
            $entity->setDetails(['msg' => $th->getMessage()]);
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
}
