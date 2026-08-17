<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Alipay\PagePayUtils;
use Siganushka\PaymentBundle\Model\PaymentInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlipayPage extends AbstractAlipay
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PagePayUtils $pagePayUtils)
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
            'qr_pay_mode' => 2,
            'notify_url' => $this->generateNotifyUrl(),
        ], $payment->context()[self::PAY_OPTIONS] ?? []);

        $url = $this->pagePayUtils->url($options);

        return compact('url');
    }
}
