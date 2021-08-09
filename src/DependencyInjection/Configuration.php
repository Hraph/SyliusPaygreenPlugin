<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\DependencyInjection;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransfer;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenShopFactory;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenTransferFactory;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenShopRepository;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenTransferRepository;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
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
                ->booleanNode('force_use_authorize')
                    ->info("Force all gateways payments to use authorise")
                    ->defaultFalse()
                ->end()
                ->booleanNode('use_insite_mode')
                    ->info("Use the integrated iFrame")
                    ->defaultFalse()
                ->end()
                ->scalarNode('order_id_prefix')
                    ->info('Prefix paygreen order id in case the transaction has already been made for an order')
                    ->defaultNull()
                ->end()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('paygreen_shop')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(PaygreenShop::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(PaygreenShopInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(PaygreenShopFactory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(PaygreenShopRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('paygreen_transfer')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(PaygreenTransfer::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(PaygreenTransferInterface::class)->cannotBeEmpty()->end()
                                        ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(PaygreenTransferFactory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(PaygreenTransferRepository::class)->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
