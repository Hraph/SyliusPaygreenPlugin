<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum;

use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClient;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class PaygreenGatewayFactory extends GatewayFactory
{
    public const FACTORY_NAME = 'paygreen';

    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => self::FACTORY_NAME,
            'payum.factory_title' => 'PayGreen',
            'payum.api_client' => '@hraph_sylius_paygreen_plugin.client.paygreen_api', // Use registered service instance
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'use_authorize' => false
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
            ];

            // Set config API and save object for ApiAwareInterface
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var PaygreenApiClientInterface $paygreenApiClient */
                $paygreenApiClient = $config['payum.api_client']; // Use service

                return $paygreenApiClient;
            };
        }
    }

}
