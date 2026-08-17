<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Model;

use Doctrine\Common\Collections\Collection;
use Siganushka\Contracts\Doctrine\ExpirableInterface;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Symfony\Contracts\Translation\TranslatableInterface;

interface PaymentInterface extends ResourceInterface, TimestampableInterface, ExpirableInterface
{
    public function getNumber(): ?string;

    public function setNumber(string $number): static;

    public function getAmount(): ?int;

    public function getRefundedAmount(): int;

    public function resetRefundedAmount(): static;

    public function getCurrency(): ?string;

    public function getGateway(): string;

    public function getDetails(): ?array;

    public function setDetails(?array $details): static;

    public function getFailedReason(): ?string;

    public function setFailedReason(string $failedReason): static;

    public function getState(): PaymentState;

    public function setState(PaymentState $state): static;

    /**
     * @return Collection<int, PaymentRefundInterface>
     */
    public function getRefunds(): Collection;

    public function addRefund(PaymentRefundInterface $refund): static;

    public function removeRefund(PaymentRefundInterface $refund): static;

    public function isRefundSupported(): bool;

    public function getRefundableAmount(): int;

    public function getType(): string;

    public function getTitle(): string|TranslatableInterface;

    public function context(): array;
}
