<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Result;

class PaymentResult
{
    public function __construct(
        private readonly ?array $data = null,
        private readonly ?array $details = null,
        private readonly bool $completed = true)
    {
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }
}
