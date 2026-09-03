<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    #[Required]
    public UrlGeneratorInterface $generator;

    public function supports(PaymentInterface $payment): bool
    {
        return true;
    }

    protected function generateNotifyUrl(): string
    {
        /** @var AsTaggedItem|null */
        $attribute = ((new \ReflectionClass($this))->getAttributes(AsTaggedItem::class)[0] ?? null)?->newInstance();
        if (null === $attribute || null === $gateway = $attribute->index) {
            throw new \RuntimeException('The index argument for AsTaggedItem cannot be empty.');
        }

        return $this->generator->generate('siganushka_payment_notify', compact('gateway'), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
