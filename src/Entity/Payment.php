<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\Contracts\Doctrine\ExpirableInterface;
use Siganushka\Contracts\Doctrine\ExpirableTrait;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\GenericBundle\Utils\ClassUtils;
use Siganushka\PaymentBundle\Enum\PaymentState;

abstract class Payment implements ResourceInterface, TimestampableInterface, ExpirableInterface
{
    use ResourceTrait;
    use ExpirableTrait;
    use TimestampableTrait;

    protected ?string $number = null;
    protected ?string $title = null;
    protected ?int $amount = null;
    protected ?int $refundAmount = null;
    protected ?string $gateway = null;
    protected ?array $details = null;
    protected PaymentState $state = PaymentState::Pending;
    protected ?string $failedReason = null;

    /**
     * @var Collection<int, PaymentRefund>
     */
    protected Collection $refunds;

    public function __construct()
    {
        $this->refunds = new ArrayCollection();
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getRefundAmount(): ?int
    {
        return $this->refundAmount;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(string $gateway): static
    {
        $this->gateway = $gateway;

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

    public function getFailedReason(): ?string
    {
        return $this->failedReason;
    }

    public function setFailedReason(string $failedReason): static
    {
        $this->failedReason = $failedReason;

        return $this;
    }

    public function getState(): PaymentState
    {
        return $this->state;
    }

    public function setState(PaymentState $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<int, PaymentRefund>
     */
    public function getRefunds(): Collection
    {
        return $this->refunds;
    }

    public function addRefund(PaymentRefund $refund): static
    {
        if (!$this->refunds->contains($refund)) {
            $this->refundAmount += $refund->isSuccessful() ? $refund->getAmount() : 0;
            $this->refunds[] = $refund;
            $refund->setPayment($this);
        }

        return $this;
    }

    public function removeRefund(PaymentRefund $refund): static
    {
        if ($this->refunds->removeElement($refund)) {
            $this->refundAmount -= $refund->isSuccessful() ? $refund->getAmount() : 0;
            if ($refund->getPayment() === $this) {
                $refund->setPayment(null);
            }
        }

        return $this;
    }

    public function getType(): string
    {
        return ClassUtils::generateAlias($this);
    }

    public function context(): array
    {
        return [];
    }
}
