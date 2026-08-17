<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Siganushka\Contracts\Doctrine\ExpirableTrait;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\GenericBundle\Utils\ClassUtils;
use Siganushka\PaymentBundle\Enum\PaymentState;
use Siganushka\PaymentBundle\Model\PaymentInterface;
use Siganushka\PaymentBundle\Model\PaymentRefundInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @template TRefund of PaymentRefundInterface = PaymentRefundInterface
 */
abstract class AbstractPayment implements PaymentInterface
{
    use ResourceTrait;
    use ExpirableTrait;
    use TimestampableTrait;

    protected ?string $number = null;
    protected ?int $amount = null;
    protected ?int $refundedAmount = null;
    protected ?string $currency = null;
    protected ?array $details = null;
    protected PaymentState $state = PaymentState::Pending;
    protected ?string $failedReason = null;

    /**
     * @var Collection<int, TRefund>
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

    public function getRefundedAmount(): int
    {
        return $this->refundedAmount ??= $this->refunds->reduce(static fn (int $carry, PaymentRefundInterface $item) => $item->isSuccessful() ? $carry + $item->getAmount() : $carry, 0);
    }

    public function resetRefundedAmount(): static
    {
        $this->refundedAmount = null;

        return $this;
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
     * @return Collection<int, TRefund>
     */
    public function getRefunds(): Collection
    {
        return $this->refunds;
    }

    /**
     * @param TRefund $refund
     */
    public function addRefund(PaymentRefundInterface $refund): static
    {
        if (!$this->refunds->contains($refund)) {
            $this->resetRefundedAmount();
            $this->refunds[] = $refund;
            $refund->setPayment($this);
        }

        return $this;
    }

    /**
     * @param TRefund $refund
     */
    public function removeRefund(PaymentRefundInterface $refund): static
    {
        if ($this->refunds->removeElement($refund)) {
            $this->resetRefundedAmount();
            if ($refund->getPayment() === $this) {
                $refund->setPayment(null);
            }
        }

        return $this;
    }

    public function isRefundSupported(): bool
    {
        return PaymentState::Succeed === $this->state && \is_int($this->amount);
    }

    public function getRefundableAmount(): int
    {
        return $this->isRefundSupported() ? max(0, $this->amount - $this->getRefundedAmount()) : 0;
    }

    public function getType(): string
    {
        return $this->__type ??= ClassUtils::generateAlias($this);
    }

    public function getTitle(): string|TranslatableInterface
    {
        return new TranslatableMessage(\sprintf('payment.type.%s', $this->getType()));
    }

    public function context(): array
    {
        return [];
    }
}
