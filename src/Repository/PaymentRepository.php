<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Entity\Payment;

/**
 * @template T of Payment = Payment
 *
 * @extends GenericEntityRepository<T>
 */
class PaymentRepository extends GenericEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        /** @var class-string<T> */
        $entityClass = Payment::class;

        parent::__construct($registry, $entityClass);
    }

    /**
     * @return T|null
     */
    public function findOneByNumber(string $number): ?Payment
    {
        return $this->findOneBy(compact('number'));
    }

    /**
     * @return T|null
     */
    public function findOneByNumberWithLock(string $number): ?Payment
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.number = :number')
            ->setParameter('number', $number)
            ->setMaxResults(1)
        ;

        $query = $qb->getQuery();
        $query->setLockMode(LockMode::PESSIMISTIC_WRITE);

        /** @var T|null */
        $entity = $query->getOneOrNullResult();

        return $entity;
    }

    public function createQueryBuilderByDto(string $alias, PaymentQueryDto $dto): QueryBuilder
    {
        $criteria = new Criteria(firstResult: 0, accessRawFieldValues: true);

        if ($dto->number) {
            $criteria->andWhere(Criteria::expr()->contains('number', $dto->number));
        }

        if ($dto->gateway) {
            $criteria->andWhere(Criteria::expr()->eq('gateway', $dto->gateway));
        }

        if ($dto->state) {
            $criteria->andWhere(Criteria::expr()->eq('state', $dto->state));
        }

        if ($dto->created?->startAt) {
            $criteria->andWhere(Criteria::expr()->gte('createdAt', $dto->created->startAt));
        }

        if ($dto->created?->endAt) {
            $criteria->andWhere(Criteria::expr()->lte('createdAt', $dto->created->endAt));
        }

        $qb = $this->createQueryBuilderWithOrderBy($alias);
        $qb->addCriteria($criteria);

        if ($dto->dtype) {
            $qb->andWhere(\sprintf('%s INSTANCE OF :dtype', $alias))
                ->setParameter('dtype', $dto->dtype)
            ;
        }

        return $qb;
    }
}
