<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Exception\UnsupportedGatewayException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;

class PaymentGatewayRegistry
{
    /**
     * @param ServiceLocator<PaymentGatewayInterface> $locator
     */
    public function __construct(
        #[AutowireLocator('siganushka_payment.gateway', defaultIndexMethod: 'getName')]
        private readonly ServiceLocator $locator)
    {
    }

    public function all(): array
    {
        return iterator_to_array($this->locator);
    }

    public function get(string $name): PaymentGatewayInterface
    {
        try {
            return $this->locator->get($name);
        } catch (ServiceNotFoundException $th) {
            throw new UnsupportedGatewayException($this, $name, $th);
        }
    }

    public function getNames(): array
    {
        return array_keys($this->locator->getProvidedServices());
    }
}
