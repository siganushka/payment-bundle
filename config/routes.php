<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Siganushka\PaymentBundle\Controller\PaymentController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('siganushka_payment_getcollection', '/payments')
        ->controller([PaymentController::class, 'getCollection'])
        ->methods(['GET'])
    ;

    $routes->add('siganushka_payment_postcollection', '/payments')
        ->controller([PaymentController::class, 'postCollection'])
        ->methods(['POST'])
    ;

    $routes->add('siganushka_payment_getitem', '/payments/{number<[0-9a-zA-Z]+>}')
        ->controller([PaymentController::class, 'getItem'])
        ->methods(['GET'])
    ;
};
