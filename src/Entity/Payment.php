<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Entity;

use Siganushka\Contracts\Doctrine\ExpirableInterface;
use Siganushka\Contracts\Doctrine\ExpirableTrait;
use Siganushka\Contracts\Doctrine\ResourceInterface;
use Siganushka\Contracts\Doctrine\ResourceTrait;
use Siganushka\Contracts\Doctrine\TimestampableInterface;
use Siganushka\Contracts\Doctrine\TimestampableTrait;
use Siganushka\GenericBundle\Utils\ClassUtils;
use Siganushka\PaymentBundle\Enum\PaymentState;

abstract class Payment implements ResourceInterface, ExpirableInterface, TimestampableInterface
{
    use ResourceTrait;
    use ExpirableTrait;
    use TimestampableTrait;

    protected ?string $number = null;
    protected ?string $title = null;
    protected ?int $amount = null;
    protected ?string $gateway = null;
    protected ?array $details = null;
    protected PaymentState $state = PaymentState::Pending;

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

    public function setDetails(array $details): static
    {
        $this->details = $details;

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

    public function getType(): string
    {
        return ClassUtils::generateAlias($this);
    }

    public function validate(): void
    {
        if (null !== $this->amount && $this->amount <= 0) {
            throw new \InvalidArgumentException('The payment amount must be greater than 0.');
        }
    }
}
