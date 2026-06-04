<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Generator\PaymentNumberGeneratorInterface;

class PaymentNumberGeneratorListener
{
    public function __construct(private readonly PaymentNumberGeneratorInterface $numberGenerator)
    {
    }

    public function __invoke(Payment $entity): void
    {
        if (!$entity->getNumber()) {
            $entity->setNumber($this->numberGenerator->generate($entity));
        }
    }
}
