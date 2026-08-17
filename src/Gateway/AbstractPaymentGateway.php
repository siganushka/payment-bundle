<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\GenericBundle\Utils\ClassUtils;
use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    #[Required]
    public UrlGeneratorInterface $generator;

    public static function getName(): string
    {
        return ClassUtils::generateAlias(static::class);
    }

    public function supports(PaymentInterface $payment): bool
    {
        return true;
    }

    protected function generateNotifyUrl(): string
    {
        $gateway = static::getName();

        return $this->generator->generate('siganushka_payment_notify', compact('gateway'), UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
