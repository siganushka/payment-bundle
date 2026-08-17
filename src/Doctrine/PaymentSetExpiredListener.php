<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PaymentSetExpiredListener
{
    public function __construct(
        #[Autowire(param: 'siganushka_payment.payment_cancel_seconds')]
        private readonly int $seconds)
    {
    }

    public function __invoke(PaymentInterface $entity): void
    {
        $defaultExpiredAt = (new \DateTimeImmutable())->modify(\sprintf('+%d seconds', $this->seconds));
        $currentExpiredAt = $entity->getExpiredAt();

        if (!$currentExpiredAt || $currentExpiredAt > $defaultExpiredAt) {
            $entity->setExpiredAt($defaultExpiredAt);
        }
    }
}
