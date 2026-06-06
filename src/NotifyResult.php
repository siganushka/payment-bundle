<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

class NotifyResult extends PaymentResult implements NotifyResultInterface
{
    public function __construct(
        private readonly bool $successful,
        private readonly array $data,
        private readonly string $paymentIdentifier,
        private readonly int $amount)
    {
        parent::__construct($successful, $data);
    }

    public function getPaymentIdentifier(): string
    {
        return $this->paymentIdentifier;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
