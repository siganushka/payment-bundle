<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Generator;

use Godruoyi\Snowflake\Snowflake;
use Siganushka\PaymentBundle\Entity\AbstractPayment;

class PaymentNumberGenerator implements PaymentNumberGeneratorInterface
{
    public function __construct(private readonly Snowflake $snowflake = new Snowflake())
    {
    }

    public function generate(AbstractPayment $entity): string
    {
        return $this->snowflake->id();
    }
}
