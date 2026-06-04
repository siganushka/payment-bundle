<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Tests\Fixtures;

class Bar
{
    public function __construct(private readonly int $price)
    {
    }

    public function getPrice(): int
    {
        return $this->price;
    }
}
