<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\DependencyInjection;

use Doctrine\ORM\Events;
use Siganushka\PaymentBundle\Doctrine\PaymentCancelMessageListener;
use Siganushka\PaymentBundle\Doctrine\PaymentNumberGeneratorListener;
use Siganushka\PaymentBundle\Doctrine\PaymentSetExpiredListener;
use Siganushka\PaymentBundle\Entity\AbstractPayment;
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

        foreach (Configuration::RESOURCE_MAPPING as $configName => [, $repositoryClass]) {
            $repositoryClass = $container->findDefinition($repositoryClass);
            $repositoryClass->setArgument('$entityClass', $config[$configName]);
        }

        $container->setParameter('siganushka_payment.payment_cancel_transport', $config['payment_cancel_transport']);
        $container->setParameter('siganushka_payment.payment_cancel_seconds', $config['payment_cancel_seconds']);

        $container->setAlias(PaymentNumberGeneratorInterface::class, $config['payment_number_generator']);

        $paymentNumberGeneratorListener = $container->findDefinition(PaymentNumberGeneratorListener::class);
        $paymentNumberGeneratorListener->addTag('doctrine.orm.entity_listener', ['event' => Events::prePersist, 'entity' => AbstractPayment::class, 'priority' => 8]);

        $paymentSetExpiredListener = $container->findDefinition(PaymentSetExpiredListener::class);
        $paymentSetExpiredListener->addTag('doctrine.orm.entity_listener', ['event' => Events::prePersist, 'entity' => AbstractPayment::class, 'priority' => 4]);

        $paymentCancelMessageListener = $container->findDefinition(PaymentCancelMessageListener::class);
        $paymentCancelMessageListener->addTag('doctrine.orm.entity_listener', ['event' => Events::postPersist, 'entity' => AbstractPayment::class, 'priority' => -256]);

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
        $config = array_merge(...$configs);

        $resolveTargetEntities = [];
        foreach (Configuration::RESOURCE_MAPPING as $configName => [$interface]) {
            $resolveTargetEntities[$interface] = $config[$configName] ?? null;
        }

        if (\count($rte = array_filter($resolveTargetEntities))) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => ['resolve_target_entities' => $rte],
            ]);
        }

        if (interface_exists(MessageBusInterface::class) && $transport = ($config['payment_cancel_transport'] ?? null)) {
            $container->prependExtensionConfig('framework', [
                'messenger' => [
                    'routing' => [
                        PaymentCancelMessage::class => $transport,
                    ],
                ],
            ]);
        }
    }
}
