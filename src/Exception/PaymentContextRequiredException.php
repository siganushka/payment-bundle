<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Exception;

use Siganushka\PaymentBundle\Entity\AbstractPayment;

class PaymentContextRequiredException extends \RuntimeException
{
    public function __construct(
        private readonly AbstractPayment $payment,
        private readonly string $contextKey)
    {
        parent::__construct(\sprintf('The context key "%s" for %s is required.', $contextKey, $payment::class));
    }

    public function getPayment(): AbstractPayment
    {
        return $this->payment;
    }

    public function getContextKey(): string
    {
        return $this->contextKey;
    }
}
