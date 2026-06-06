<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Siganushka\PaymentBundle\Entity\Payment;

class PaymentSetExpiredListener
{
    public function __construct(private readonly int $seconds)
    {
    }

    public function __invoke(Payment $entity): void
    {
        $defaultExpiredAt = (new \DateTimeImmutable())->modify(\sprintf('+%d seconds', $this->seconds));
        $currentExpiredAt = $entity->getExpiredAt();

        if (!$currentExpiredAt || $currentExpiredAt > $defaultExpiredAt) {
            $entity->setExpiredAt($defaultExpiredAt);
        }
    }
}
