<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Wxpay\ParameterUtils;
use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlipayApp extends AbstractAlipay
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ParameterUtils $parameterUtils)
    {
    }

    public function pay(PaymentInterface $payment): array
    {
        $title = $payment->getTitle();
        if ($title instanceof TranslatableInterface) {
            $title = $title->trans($this->translator);
        }

        $options = array_merge([
            'subject' => $title,
            'out_trade_no' => $payment->getNumber(),
            'total_amount_as_cents' => $payment->getAmount(),
            'notify_url' => $this->generateNotifyUrl(),
        ], $payment->context()[self::PAY_OPTIONS] ?? []);

        return $this->parameterUtils->app($options);
    }
}
