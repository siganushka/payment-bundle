<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Factory;

use Siganushka\PaymentBundle\Entity\Payment;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @param iterable<PaymentFactoryInterface> $factories
     */
    public function __construct(
        #[AutowireIterator('siganushka_payment.factory')]
        private readonly iterable $factories)
    {
    }

    public function createPayment(string $type, int|string $identifier, string $gateway): Payment
    {
        foreach ($this->factories as $factory) {
            if ($factory->supportsType($type)) {
                return $factory->createPayment($type, $identifier, $gateway);
            }
        }

        throw new \InvalidArgumentException(\sprintf('There is no factory with identifier "%s".', $identifier));
    }

    public function supportsType(string $type): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supportsType($type)) {
                return true;
            }
        }

        return false;
    }
}
