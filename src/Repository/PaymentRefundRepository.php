<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\PaymentBundle\Model\PaymentInterface;
use Siganushka\PaymentBundle\Model\PaymentRefundInterface;

/**
 * @template T of PaymentRefundInterface = PaymentRefundInterface
 *
 * @extends GenericEntityRepository<T>
 */
class PaymentRefundRepository extends GenericEntityRepository
{
    public function createFromPayment(PaymentInterface $payment): PaymentRefundInterface
    {
        $refundCount = \count($payment->getRefunds());
        $refundNumber = \sprintf('%s%02d', $payment->getNumber(), ++$refundCount);

        $refund = $this->createNew($payment);
        $refund->setNumber($refundNumber);

        return $refund;
    }
}
