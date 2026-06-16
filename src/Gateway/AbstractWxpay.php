<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\ApiFactory\Wxpay\NotifyHandler;
use Siganushka\ApiFactory\Wxpay\Refund;
use Siganushka\ApiFactory\Wxpay\Unifiedorder;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Exception\PaymentFailedException;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Siganushka\PaymentBundle\Result\RefundNotifyResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractWxpay extends AbstractPaymentGateway
{
    public const PAY_OPTIONS = 'wxpay_pay_options';
    public const REFUND_OPTIONS = 'wxpay_refund_options';

    #[Required]
    public UrlGeneratorInterface $generator;
    #[Required]
    public Unifiedorder $unifiedorder;
    #[Required]
    public Refund $wxpayRefund;
    #[Required]
    public NotifyHandler $notifyHandler;
    #[Required]
    #[Autowire(param: 'kernel.debug')]
    public bool $debug;

    public function refund(Payment $payment, PaymentRefund $refund): array
    {
        $options = array_merge([
            'out_trade_no' => $payment->getNumber(),
            'total_fee' => $payment->getAmount(),
            'out_refund_no' => $refund->getNumber(),
            'refund_fee' => $refund->getAmount(),
        ], $payment->context()[self::REFUND_OPTIONS] ?? []);

        try {
            return $this->wxpayRefund->send($options);
        } catch (ParseResponseException $th) {
            throw new PaymentFailedException($th->getMessage(), $th->getResponseData());
        }
    }

    public function notify(Request $request): NotifyResult
    {
        $data = $this->notifyHandler->handle($request, verifySignature: !$this->debug);

        if (\array_key_exists('out_trade_no', $data)
            && \array_key_exists('total_fee', $data)
            && \array_key_exists('result_code', $data)) {
            return new NotifyResult('SUCCESS' === $data['result_code'], $data['out_trade_no'], (int) $data['total_fee'], $data);
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

    protected function doPay(Payment $payment): array
    {
        $options = array_merge([
            'body' => $payment->getTitle(),
            'out_trade_no' => $payment->getNumber(),
            'total_fee' => $payment->getAmount(),
            'trade_type' => $this->getTradeType(),
            'notify_url' => $this->generateNotifyUrl($this->generator),
        ], $payment->context()[self::PAY_OPTIONS] ?? []);

        return $this->unifiedorder->send($options);
    }

    abstract protected function getTradeType(): string;
}
