<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Doctrine;

use Siganushka\PaymentBundle\Entity\AbstractPayment;
use Siganushka\PaymentBundle\Generator\PaymentNumberGeneratorInterface;

class PaymentNumberGeneratorListener
{
    public function __construct(private readonly PaymentNumberGeneratorInterface $numberGenerator)
    {
    }

    public function __invoke(AbstractPayment $entity): void
    {
        if (!$entity->getNumber()) {
            $entity->setNumber($this->numberGenerator->generate($entity));
        }
    }
}
