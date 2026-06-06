<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle;

interface NotifyResultInterface extends PaymentResultInterface
{
    public function getPaymentIdentifier(): int|string;

    public function getAmount(): int;
}
