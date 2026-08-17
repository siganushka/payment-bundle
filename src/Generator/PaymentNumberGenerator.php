<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Generator;

use Godruoyi\Snowflake\Snowflake;
use Siganushka\PaymentBundle\Model\PaymentInterface;

class PaymentNumberGenerator implements PaymentNumberGeneratorInterface
{
    public function __construct(private readonly Snowflake $snowflake = new Snowflake())
    {
    }

    public function generate(PaymentInterface $entity): string
    {
        return $this->snowflake->id();
    }
}
