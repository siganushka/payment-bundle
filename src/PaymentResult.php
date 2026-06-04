<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

class PaymentResult implements PaymentResultInterface
{
    public function __construct(private readonly bool $successful, private readonly mixed $data)
    {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
