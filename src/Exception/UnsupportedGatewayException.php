<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Exception;

use Siganushka\PaymentBundle\Gateway\PaymentGatewayRegistry;

class UnsupportedGatewayException extends \InvalidArgumentException
{
    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
        private readonly string $gateway)
    {
        parent::__construct(\sprintf('The gateway "%s" is invalid.', $gateway));
    }

    public function getRegistry(): PaymentGatewayRegistry
    {
        return $this->registry;
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }
}
