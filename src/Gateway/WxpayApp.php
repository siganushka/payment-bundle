<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Wxpay\ParameterUtils;
use Siganushka\PaymentBundle\Entity\Payment;

class WxpayApp extends AbstractWxpay
{
    public function __construct(private readonly ParameterUtils $parameterUtils)
    {
    }

    public function pay(Payment $payment): array
    {
        $result = $this->doPay($payment);
        $prepay_id = $result['prepay_id'] ?? null;

        $data = $this->parameterUtils->app(compact('prepay_id'));

        return $data;
    }

    protected function getTradeType(): string
    {
        return 'JSAPI';
    }
}
