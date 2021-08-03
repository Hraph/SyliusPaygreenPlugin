<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class SyliusPaygreenPluginExtension extends AbstractResourceExtension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // Create parameter for api client with config
        $container->setParameter("hraph_sylius_paygreen_plugin.client.username", $config['api']['username']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.api_key", $config['api']['api_key']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.sandbox", $config['api']['sandbox']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.payment_type", $config['payment_type']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.force_use_authorize", $config['force_use_authorize']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.use_insite_mode", $config['use_insite_mode']);
        $container->setParameter("hraph_sylius_paygreen_plugin.client.order_id_prefix", $config['order_id_prefix']);

        $this->registerResources('hraph_sylius_paygreen_plugin', 'doctrine/orm', $config['resources'], $container);
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
