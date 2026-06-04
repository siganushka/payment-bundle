<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Message;

final class PaymentCancelMessage
{
    public function __construct(private readonly string $number)
    {
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
