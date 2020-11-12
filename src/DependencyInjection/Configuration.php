<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_paygreen_plugin');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode("api")
                    ->children()
                        ->scalarNode('username')
                            ->info("Username of the main shop")
                            ->defaultValue("username")
                        ->end()
                        ->scalarNode('api_key')
                            ->info("API Key of the main shop")
                            ->defaultValue("key")
                        ->end()
                        ->booleanNode('sandbox')
                            ->info("Use sandbox mode")
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('payment_type')
                    ->info("Type of payment")
                    ->defaultValue("CB")
                ->end()
                ->scalarNode('force_use_authorize')
                    ->info("Force all gateways payments to use authorise")
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
