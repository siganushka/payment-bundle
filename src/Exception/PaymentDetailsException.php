<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Exception;

class PaymentDetailsException extends \RuntimeException
{
    public function __construct(private readonly ?array $details, string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }
}
