<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\Payment;
use Symfony\Component\HttpFoundation\Response;

class WxpayNative extends AbstractWxpay
{
    public function pay(Payment $payment): array
    {
        $result = $this->doPay($payment, ['trade_type' => 'NATIVE']);
        // Only reserve code_url to response.
        $data = array_filter($result, static fn (string $key) => 'code_url' === $key, \ARRAY_FILTER_USE_KEY);

        return $data;
    }
}
