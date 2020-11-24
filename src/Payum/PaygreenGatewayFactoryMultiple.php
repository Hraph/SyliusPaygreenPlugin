<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum;

use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class PaygreenGatewayFactoryMultiple extends GatewayFactory
{
    public const FACTORY_NAME = 'paygreen_multiple';

    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'PayGreen - Multiple',
            'payum.api_client' => '@hraph_sylius_paygreen_plugin.client.paygreen_api_factory', // Use registered service instance
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'use_authorize' => false
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                'times'
            ];

            // Set config API and save object for ApiAwareInterface
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var PaygreenApiFactoryInterface $factory */
                $factory = $config['payum.api_client']; // Use service
                $factoryOptions = $factory->getOptions();

                $factoryOptions->setIsMultiplePaymentTime(true);
                $factoryOptions->setMultiplePaymentTimes($config['times']);

                return $factory;
            };
        }
    }

}
