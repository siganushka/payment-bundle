<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Siganushka\PaymentBundle\Generator\PaymentNumberGeneratorInterface;
use Siganushka\PaymentBundle\Model\PaymentInterface;

class PaymentNumberGeneratorListener
{
    public function __construct(private readonly PaymentNumberGeneratorInterface $numberGenerator)
    {
    }

    public function __invoke(PaymentInterface $entity): void
    {
        if (!$entity->getNumber()) {
            $entity->setNumber($this->numberGenerator->generate($entity));
        }
    }
}
