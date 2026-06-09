<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\GenericBundle\Utils\ClassUtils;
use Siganushka\PaymentBundle\Entity\Payment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    public static function getName(): string
    {
        return ClassUtils::generateAlias(static::class);
    }

    public function supports(Payment $payment): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return static::getName();
    }

    protected function generateNotifyUrl(UrlGeneratorInterface $generator): string
    {
        $gateway = static::getName();

        return $generator->generate('siganushka_payment_notify', compact('gateway'), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
