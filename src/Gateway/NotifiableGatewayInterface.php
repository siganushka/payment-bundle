<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Result\NotifyResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface NotifiableGatewayInterface
{
    public function notify(Request $request): NotifyResult;

    public function notifyResponse(bool $successful, ?string $message = null): Response;
}
