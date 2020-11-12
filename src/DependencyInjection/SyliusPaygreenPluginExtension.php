<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class SyliusPaygreenPluginExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // Create parameter for api client with config
        $container->setParameter("hraph_sylius_paygreen_plugin.client.username", $mergedConfig['api']['username']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.api_key", $mergedConfig['api']['api_key']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.sandbox", $mergedConfig['api']['sandbox']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.payment_type", $mergedConfig['payment_type']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.force_use_authorize", $mergedConfig['force_use_authorize']);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * Override alias for config root node
     * @return string
     */
    public function getAlias()
    {
        return 'sylius_paygreen';
    }
}
