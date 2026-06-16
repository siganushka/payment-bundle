<?php

declare(strict_types=1);

namespace Siganushka\PaymentBundle\DependencyInjection;

use Siganushka\PaymentBundle\Generator\PaymentNumberGenerator;
use Siganushka\PaymentBundle\Generator\PaymentNumberGeneratorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder<'array'>
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('siganushka_payment');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode->children()
            ->scalarNode('payment_number_generator')
                ->cannotBeEmpty()
                ->defaultValue(PaymentNumberGenerator::class)
                ->validate()
                    ->ifTrue(static fn (mixed $v): bool => \is_string($v) && !is_subclass_of($v, PaymentNumberGeneratorInterface::class, true))
                    ->thenInvalid('The value must be instanceof '.PaymentNumberGeneratorInterface::class.', %s given.')
                ->end()
            ->end()
            ->stringNode('payment_cancel_transport')
                ->defaultNull()
            ->end()
            ->integerNode('payment_cancel_seconds')
                ->defaultValue(3600)
                ->validate()
                    ->ifTrue(static fn (mixed $v): bool => \is_int($v) && $v <= 0)
                    ->thenInvalid('The value must be greater than 0, %s given.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
