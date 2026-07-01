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
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

abstract class Payment implements ResourceInterface, TimestampableInterface, ExpirableInterface
{
    use ResourceTrait;
    use ExpirableTrait;
    use TimestampableTrait;

    protected ?string $number = null;
    protected ?int $amount = null;
    protected ?int $refundAmount = null;
    protected ?string $currency = null;
    protected ?array $details = null;
    protected PaymentState $state = PaymentState::Pending;
    protected ?string $failedReason = null;

    /**
     * @var Collection<int, PaymentRefund>
     */
    protected Collection $refunds;

    /**
     * @var string Cached inheritance type
     */
    private string $__type;

    public function __construct(protected readonly string $gateway)
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getRefundAmount(): ?int
    {
        return $this->refundAmount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getGateway(): string
    {
        return $this->gateway;
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

    public function getRefundableAmount(): ?int
    {
        return PaymentState::Succeed === $this->state && \is_int($this->amount)
            ? $this->amount - $this->refundAmount
            : null;
    }

    public function getType(): string
    {
        return $this->__type ??= ClassUtils::generateAlias($this);
    }

    public function getTitle(): string|TranslatableInterface
    {
        return new TranslatableMessage(\sprintf('payment.type.%s', $this->getType()), $this->getTitleParameters());
    }

    public function getTitleParameters(): array
    {
        return [];
    }

    public function context(): array
    {
        return [];
    }

    public function supportsRefund(): bool
    {
        return true;
    }
}
