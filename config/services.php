<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Siganushka\PaymentBundle\SiganushkaPaymentBundle;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
    ;

    $ref = new \ReflectionClass(SiganushkaPaymentBundle::class);
    $services->load($ref->getNamespaceName().'\\', '../src/')
        ->exclude([
            '../src/DependencyInjection/',
            '../src/Dto/',
            '../src/Entity/',
            '../src/Event/',
            '../src/Exception/',
            '../src/Message/',
            '../src/PaymentResult.php',
            '../src/PaymentResultInterface.php',
            '../src/SiganushkaPaymentBundle.php',
        ]);
};
