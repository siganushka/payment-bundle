<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Model\PaymentInterface;
use Siganushka\PaymentBundle\Model\PaymentRefundInterface;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PaymentGatewayInterface
{
    public static function getName(): string;

    public function supports(PaymentInterface $payment): bool;

    public function pay(PaymentInterface $payment): array;

    public function refund(PaymentInterface $payment, PaymentRefundInterface $refund): array;

    public function notify(Request $request): NotifyResult;

    public function notifyResponse(bool $successful, ?string $message = null): Response;
}
