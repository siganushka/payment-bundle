<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Form;

use Siganushka\PaymentBundle\Repository\PaymentRefundRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentRefundType extends AbstractType
{
    public function __construct(private readonly PaymentRefundRepository $repository)
    {
        throw new \Exception('Not implemented');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'payment_refund.amount',
            ])
            ->add('note', TextareaType::class, [
                'label' => 'payment_refund.note',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->repository->getClassName(),
        ]);
    }
}
