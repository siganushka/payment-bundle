<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Alipay\NotifyHandler;
use Siganushka\ApiFactory\Alipay\PagePayUtils;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Siganushka\PaymentBundle\Result\PayNotifyResult;
use Siganushka\PaymentBundle\Result\RefundNotifyResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AlipayPage extends AbstractAlipay
{
    public function __construct(
        private readonly UrlGeneratorInterface $generator,
        private readonly PagePayUtils $pagePayUtils,
        private readonly NotifyHandler $notifyHandler)
    {
    }

    public function pay(Payment $payment): array
    {
        $options = [
            'subject' => $payment->getTitle(),
            'out_trade_no' => $payment->getNumber(),
            'total_amount_as_cents' => $payment->getAmount(),
            'qr_pay_mode' => 2,
            'notify_url' => $this->generateNotifyUrl($this->generator),
        ];

        $url = $this->pagePayUtils->url($options);

        return compact('url');
    }

    public function refund(Payment $payment): array
    {
        throw new \BadMethodCallException('Unsupported method.');
    }

    public function notify(Request $request): NotifyResult
    {
        $data = $this->notifyHandler->handle($request, verifySignature: false);
        $toCents = static fn (string $key) => (int) ($data[$key] * 100);

        if (\array_key_exists('out_biz_no', $data)
            && \array_key_exists('refund_fee', $data)
            && \array_key_exists('trade_status', $data)) {
            return new RefundNotifyResult('TRADE_SUCCESS' === $data['trade_status'], $data['out_biz_no'], $toCents('refund_fee'), $data);
        }

        if (\array_key_exists('out_trade_no', $data)
            && \array_key_exists('total_amount', $data)
            && \array_key_exists('trade_status', $data)) {
            return new PayNotifyResult('TRADE_SUCCESS' === $data['trade_status'], $data['out_trade_no'], $toCents('total_amount'), $data);
        }

        throw new \RuntimeException('Invalid request.');
    }

    public function notifyResponse(bool $successful, ?string $message = null): Response
    {
        return \call_user_func($successful
            ? $this->notifyHandler->success(...)
            : $this->notifyHandler->fail(...));
    }
}
