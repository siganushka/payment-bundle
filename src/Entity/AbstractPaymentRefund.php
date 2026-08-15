<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Entity;

use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;

/**
 * @template TPayment of AbstractPayment = AbstractPayment
 */
abstract class AbstractPaymentRefund implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    /**
     * @var TPayment|null
     */
    protected ?AbstractPayment $payment = null;
    protected ?string $number = null;
    protected ?int $amount = null;
    protected ?array $details = null;
    protected bool $successful = false;
    protected ?string $failedReason = null;
    protected ?string $note = null;

    /**
     * @param TPayment|null $payment
     */
    public function __construct(?AbstractPayment $payment = null)
    {
        $this->payment = $payment;
    }

    /**
     * @return TPayment|null
     */
    public function getPayment(): ?AbstractPayment
    {
        return $this->payment;
    }

    /**
     * @param TPayment|null $payment
     */
    public function setPayment(?AbstractPayment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function setSuccessful(bool $successful): static
    {
        $this->successful = $successful;
        $this->payment?->resetRefundedAmount();

        return $this;
    }

    public function getFailedReason(): ?string
    {
        return $this->failedReason;
    }

    public function setFailedReason(?string $failedReason): static
    {
        $this->failedReason = $failedReason;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }
}
