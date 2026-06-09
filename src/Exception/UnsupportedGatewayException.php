<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Exception;

use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;

class UnsupportedGatewayException extends \InvalidArgumentException
{
    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
        private readonly string $gateway,
        ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('The gateway "%s" is invalid. Accepted values are: "%s".', $gateway, implode('", "', $registry->getNames())), 0, $previous);
    }

    public function getRegistry(): PaymentGatewayRegistry
    {
        return $this->registry;
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }

    public function getSafeMessage(): string
    {
        return \sprintf('The gateway "%s" is not supported.', $this->gateway);
    }
}
