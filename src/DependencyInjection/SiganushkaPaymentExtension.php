<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\DependencyInjection;

use Doctrine\ORM\Events;
use Siganushka\PaymentBundle\Doctrine\PaymentCancelMessageListener;
use Siganushka\PaymentBundle\Doctrine\PaymentNumberGeneratorListener;
use Siganushka\PaymentBundle\Doctrine\PaymentSetExpiredListener;
use Siganushka\PaymentBundle\Entity\Payment;
use Siganushka\PaymentBundle\Factory\PaymentFactoryInterface;
use Siganushka\PaymentBundle\Gateway\PaymentGatewayInterface;
use Siganushka\PaymentBundle\Generator\PaymentNumberGeneratorInterface;
use Siganushka\PaymentBundle\Message\PaymentCancelMessage;
use Siganushka\PaymentBundle\MessageHandler\PaymentCancelMessageHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Messenger\MessageBusInterface;

class SiganushkaPaymentExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('siganushka_payment.payment_cancel_transport', $config['payment_cancel_transport']);
        $container->setParameter('siganushka_payment.payment_cancel_seconds', $config['payment_cancel_seconds']);

        $container->setAlias(PaymentNumberGeneratorInterface::class, $config['payment_number_generator']);

        $paymentNumberGeneratorListener = $container->findDefinition(PaymentNumberGeneratorListener::class);
        $paymentNumberGeneratorListener->addTag('doctrine.orm.entity_listener', ['event' => Events::prePersist, 'entity' => Payment::class, 'priority' => 8]);

        $paymentSetExpiredListener = $container->findDefinition(PaymentSetExpiredListener::class);
        $paymentSetExpiredListener->setArgument('$seconds', $config['payment_cancel_seconds']);
        $paymentSetExpiredListener->addTag('doctrine.orm.entity_listener', ['event' => Events::prePersist, 'entity' => Payment::class, 'priority' => 4]);

        $paymentCancelMessageListener = $container->findDefinition(PaymentCancelMessageListener::class);
        $paymentCancelMessageListener->addTag('doctrine.orm.entity_listener', ['event' => Events::postPersist, 'entity' => Payment::class, 'priority' => -256]);

        if (!interface_exists(MessageBusInterface::class) || !$config['payment_cancel_transport']) {
            $container->removeDefinition(PaymentCancelMessageListener::class);
            $container->removeDefinition(PaymentCancelMessageHandler::class);
        }

        $container->registerForAutoconfiguration(PaymentFactoryInterface::class)
            ->addTag('siganushka_payment.factory')
        ;

        $container->registerForAutoconfiguration(PaymentGatewayInterface::class)
            ->addTag('siganushka_payment.gateway')
        ;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (interface_exists(MessageBusInterface::class) && $config['payment_cancel_transport']) {
            $container->prependExtensionConfig('framework', [
                'messenger' => [
                    'routing' => [
                        PaymentCancelMessage::class => $config['payment_cancel_transport'],
                    ],
                ],
            ]);
        }
    }
}
