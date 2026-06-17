<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Exception;

use Siganushka\PaymentBundle\Entity\Payment;

class PaymentContextRequiredException extends \RuntimeException
{
    public function __construct(
        private readonly Payment $payment,
        private readonly string $contextKey)
    {
        parent::__construct(\sprintf('The context key "%s" for %s is required.', $contextKey, $payment::class));
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getContextKey(): string
    {
        return $this->contextKey;
    }
}
