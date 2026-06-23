<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Gateway;

use Siganushka\ApiFactory\Alipay\Exception\InvalidSignatureException;
use Siganushka\ApiFactory\Alipay\NotifyHandler;
use Siganushka\ApiFactory\Alipay\Refund;
use Siganushka\ApiFactory\Exception\ParseResponseException;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Siganushka\PaymentBundle\Exception\PaymentFailedException;
use Siganushka\PaymentBundle\Result\NotifyResult;
use Siganushka\PaymentBundle\Result\RefundNotifyResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractAlipay extends AbstractPaymentGateway
{
    public const PAY_OPTIONS = 'alipay_pay_options';
    public const REFUND_OPTIONS = 'alipay_refund_options';

    #[Required]
    public Refund $alipayRefund;
    #[Required]
    public NotifyHandler $notifyHandler;
    #[Required]
    #[Autowire(param: 'kernel.debug')]
    public bool $debug;

    public function refund(Payment $payment, PaymentRefund $refund): array
    {
        $options = array_merge([
            'out_trade_no' => $payment->getNumber(),
            'refund_amount_as_cents' => $refund->getAmount(),
        ], $payment->context()[self::REFUND_OPTIONS] ?? []);

        try {
            return $this->alipayRefund->send($options);
        } catch (ParseResponseException $th) {
            throw new PaymentFailedException($th->getMessage(), $th->getResponseData());
        }
    }

    public function notify(Request $request): NotifyResult
    {
        try {
            $data = $this->notifyHandler->handle($request, verifySignature: !$this->debug);
        } catch (InvalidSignatureException $th) {
            throw new PaymentFailedException($th->getMessage(), $th->getData());
        }

        $asCents = static fn (string $key) => (int) ($data[$key] * 100);
        if (\array_key_exists('out_biz_no', $data)
            && \array_key_exists('refund_fee', $data)
            && \array_key_exists('trade_status', $data)) {
            return new RefundNotifyResult('TRADE_SUCCESS' === $data['trade_status'], $data['out_biz_no'], $asCents('refund_fee'), $data);
        }

        if (\array_key_exists('out_trade_no', $data)
            && \array_key_exists('total_amount', $data)
            && \array_key_exists('trade_status', $data)) {
            return new NotifyResult('TRADE_SUCCESS' === $data['trade_status'], $data['out_trade_no'], $asCents('total_amount'), $data);
        }

        throw new PaymentFailedException('Invalid request.', $data);
    }

    public function notifyResponse(bool $successful, ?string $message = null): Response
    {
        return \call_user_func($successful
            ? $this->notifyHandler->success(...)
            : $this->notifyHandler->fail(...));
    }
}
