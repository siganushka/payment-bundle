<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Model;

use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\TimestampableInterface;

interface PaymentRefundInterface extends ResourceInterface, TimestampableInterface
{
    public function getPayment(): ?PaymentInterface;

    public function setPayment(?PaymentInterface $payment): static;

    public function getNumber(): ?string;

    public function setNumber(string $number): static;

    public function getAmount(): ?int;

    public function setAmount(?int $amount): static;

    public function getDetails(): ?array;

    public function setDetails(?array $details): static;

    public function isSuccessful(): bool;

    public function setSuccessful(bool $successful): static;

    public function getFailedReason(): ?string;

    public function setFailedReason(?string $failedReason): static;

    public function getNote(): ?string;

    public function setNote(?string $note): static;
}
