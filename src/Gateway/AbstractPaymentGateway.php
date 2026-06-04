<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\GenericBundle\Utils\ClassUtils;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    public static function getName(): string
    {
        return ClassUtils::generateAlias(static::class);
    }
}
