<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Psr\Log\LoggerInterface;
use Siganushka\PaymentBundle\Entity\Payment;

class PaymentSetExpiredListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly int $seconds)
    {
    }

    public function __invoke(Payment $entity): void
    {
        $defaultExpiredAt = (new \DateTimeImmutable())->modify(\sprintf('+%d seconds', $this->seconds));
        $currentExpiredAt = $entity->getExpiredAt();

        if (!$currentExpiredAt || $currentExpiredAt > $defaultExpiredAt) {
            $entity->setExpiredAt($defaultExpiredAt);
        }

        $this->logger->info(__METHOD__, [
            'expiredAt' => $entity->getExpiredAt()?->format('Y-m-d H:i:s'),
        ]);
    }
}
