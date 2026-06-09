<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\Form;

use Siganushka\PaymentBundle\Entity\PaymentRefund;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentRefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'constraints' => [
                    new NotBlank(),
                    new LessThanOrEqual($options['max_amount']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentRefund::class,
        ]);

        $resolver->setRequired('max_amount');
        $resolver->setAllowedTypes('max_amount', 'int');
    }
}
