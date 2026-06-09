<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Result;

class NotifyResult
{
    public function __construct(
        private readonly bool $successful,
        private readonly string $number,
        private readonly int $amount,
        private readonly ?array $details = null)
    {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }
}
