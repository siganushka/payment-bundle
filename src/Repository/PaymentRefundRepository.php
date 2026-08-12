<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Repository;

use Siganushka\GenericBundle\Repository\GenericEntityRepository;
use Siganushka\PaymentBundle\Entity\AbstractPayment;
use Siganushka\PaymentBundle\Entity\AbstractPaymentRefund;

/**
 * @template T of AbstractPaymentRefund = AbstractPaymentRefund
 *
 * @extends GenericEntityRepository<T>
 */
class PaymentRefundRepository extends GenericEntityRepository
{
    public function createFromPayment(AbstractPayment $payment): AbstractPaymentRefund
    {
        $refundCount = \count($payment->getRefunds());
        $refundNumber = \sprintf('%s%02d', $payment->getNumber(), ++$refundCount);

        $refund = $this->createNew($payment);
        $refund->setNumber($refundNumber);

        return $refund;
    }
}
