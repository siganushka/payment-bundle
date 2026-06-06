<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

interface PaymentResultInterface
{
    public function isSuccessful(): bool;

    public function getData(): array;
}
