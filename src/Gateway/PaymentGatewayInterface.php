<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PaymentGatewayInterface extends \Stringable
{
    public static function getName(): string;

    public function supports(Payment $payment): bool;

    public function pay(Payment $payment): array;

    public function refund(Payment $payment, PaymentRefund $refund): array;

    public function notify(Request $request): NotifyResult;

    public function notifyResponse(bool $successful, ?string $message = null): Response;
}
