<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Wxpay\NotifyHandler;
use Siganushka\ApiFactory\Wxpay\Unifiedorder;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Siganushka\PaymentBundle\Result\PayNotifyResult;
use Siganushka\PaymentBundle\Result\RefundNotifyResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WxpayJsapi extends AbstractWxpay
{
    public function __construct(
        private readonly UrlGeneratorInterface $generator,
        private readonly Unifiedorder $unifiedorder,
        private readonly NotifyHandler $notifyHandler)
    {
    }

    public function pay(Payment $payment): array
    {
        $options = [
            'body' => $payment->getTitle(),
            'out_trade_no' => $payment->getNumber(),
            'total_fee' => $payment->getAmount(),
            'trade_type' => 'NATIVE',
            'notify_url' => $this->generateNotifyUrl($this->generator),
        ];

        $result = $this->unifiedorder->send($options);
        // Only reserve code_url to response.
        $data = array_filter($result, static fn (string $key) => 'code_url' === $key, \ARRAY_FILTER_USE_KEY);

        return $data;
    }

    public function refund(Payment $payment): array
    {
        throw new \BadMethodCallException('Unsupported method.');
    }

    public function notify(Request $request): NotifyResult
    {
        $data = $this->notifyHandler->handle($request);

        if (\array_key_exists('out_trade_no', $data)
            && \array_key_exists('total_fee', $data)
            && \array_key_exists('result_code', $data)) {
            return new PayNotifyResult('SUCCESS' === $data['result_code'], $data['out_trade_no'], (int) $data['total_fee'], $data);
        }

        // @see https://pay.weixin.qq.com/doc/v2/merchant/4011935223
        if (\array_key_exists('req_info', $data)) {
            $data = $this->notifyHandler->decryptReqInfo($data['req_info']);
        }

        if (\array_key_exists('out_refund_no', $data)
            && \array_key_exists('refund_fee', $data)
            && \array_key_exists('refund_status', $data)) {
            return new RefundNotifyResult('SUCCESS' === $data['refund_status'], $data['out_refund_no'], (int) $data['refund_fee'], $data);
        }

        throw new \RuntimeException('Invalid request.');
    }

    public function notifyResponse(bool $successful, ?string $message = null): Response
    {
        return \call_user_func($successful
            ? $this->notifyHandler->success(...)
            : $this->notifyHandler->fail(...), $message);
    }
}
