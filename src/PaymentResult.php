<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

class PaymentResult implements PaymentResultInterface
{
    public function __construct(
        private readonly bool $successful,
        private readonly array $data)
    {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
