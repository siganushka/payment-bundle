<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\AbstractPayment;
use Siganushka\PaymentBundle\Entity\AbstractPaymentRefund;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PaymentGatewayInterface extends \Stringable
{
    public static function getName(): string;

    public function supports(AbstractPayment $payment): bool;

    public function pay(AbstractPayment $payment): array;

    public function refund(AbstractPayment $payment, AbstractPaymentRefund $refund): array;

    public function notify(Request $request): NotifyResult;

    public function notifyResponse(bool $successful, ?string $message = null): Response;
}
