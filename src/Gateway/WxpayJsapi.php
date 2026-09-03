<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Wxpay\ParameterUtils;
use Siganushka\PaymentBundle\Exception\PaymentContextRequiredException;
use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('wxpay_jsapi')]
class WxpayJsapi extends AbstractWxpay
{
    public const OPTIONS_OPENID = 'openid';

    public function __construct(private readonly ParameterUtils $parameterUtils)
    {
    }

    public function pay(PaymentInterface $payment): array
    {
        $openid = $payment->context()[self::OPTIONS_OPENID] ?? null;
        if (null === $openid) {
            throw new PaymentContextRequiredException($payment, self::OPTIONS_OPENID);
        }

        $result = $this->doPay($payment, ['trade_type' => 'JSAPI'] + compact('openid'));
        $prepay_id = $result['prepay_id'] ?? null;

        $data = $this->parameterUtils->jsapi(compact('prepay_id'));

        return $data;
    }
}
