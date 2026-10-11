<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\PaymentBundle\Dto\PaymentQueryDto;
use Siganushka\PaymentBundle\Model\PaymentInterface;

/**
 * @template T of PaymentInterface = PaymentInterface
 *
 * @extends GenericEntityRepository<T>
 */
class PaymentRepository extends GenericEntityRepository
{
    /**
     * @return T|null
     */
    public function findOneByNumber(mixed $number): ?PaymentInterface
    {
        return $this->findOneBy(compact('number'));
    }

    /**
     * @return T|null
     */
    public function findOneByNumberWithLock(mixed $number): ?PaymentInterface
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

    public function createQueryBuilderFromDto(string $alias, PaymentQueryDto $dto): QueryBuilder
    {
        $criteria = self::createCriteriaFromDto($dto);

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
