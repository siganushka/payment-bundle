<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Alipay\PagePayUtils;
use Siganushka\PaymentBundle\Entity\Payment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlipayPage extends AbstractAlipay
{
    public function __construct(
        private readonly UrlGeneratorInterface $generator,
        private readonly TranslatorInterface $translator,
        private readonly PagePayUtils $pagePayUtils)
    {
    }

    public function pay(Payment $payment): array
    {
        $title = $payment->getTitle();
        if ($title instanceof TranslatableInterface) {
            $title = $title->trans($this->translator);
        }

        $options = [
            'subject' => $title,
            'out_trade_no' => $payment->getNumber(),
            'total_amount_as_cents' => $payment->getAmount(),
            'qr_pay_mode' => 2,
            'notify_url' => $this->generateNotifyUrl($this->generator),
        ];

        $url = $this->pagePayUtils->url($options);

        return compact('url');
    }
}
