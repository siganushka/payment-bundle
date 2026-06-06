<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\NotifyResult;
use Siganushka\PaymentBundle\PaymentResultInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PaymentGatewayInterface
{
    public static function getName(): string;

    public function pay(Payment $payment): PaymentResultInterface;

    public function refund(Payment $payment): PaymentResultInterface;

    public function notify(Request $request): NotifyResult;

    public function createNotifyResponse(bool $successful, ?string $message = null): Response;

    public function supports(Payment $payment): bool;
}
