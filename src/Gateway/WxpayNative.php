<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Result\PaymentResult;
use Symfony\Component\HttpFoundation\Response;

class WxpayNative extends AbstractWxpay
{
    public function pay(Payment $payment): PaymentResult
    {
        $result = $this->doPay($payment);
        // Only reserve code_url to response.
        $data = array_filter($result, static fn (string $key) => 'code_url' === $key, \ARRAY_FILTER_USE_KEY);

        return new PaymentResult($data, $result, false);
    }

    protected function getTradeType(): string
    {
        return 'NATIVE';
    }
}
