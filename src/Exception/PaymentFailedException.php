<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Exception;

class PaymentFailedException extends \RuntimeException
{
    public function __construct(
        private readonly string $failedReason,
        private readonly ?array $details = null)
    {
        parent::__construct($failedReason);
    }

    public function getFailedReason(): ?string
    {
        return $this->failedReason;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }
}
