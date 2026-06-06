<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Siganushka\PaymentBundle\Controller\PaymentNotifyController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('siganushka_payment_notify', '/payments/{gateway}/notify')
        ->controller([PaymentNotifyController::class, 'notify'])
    ;
};
