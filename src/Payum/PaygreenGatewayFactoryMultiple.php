<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum;

use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClient;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\CaptureAction;
use Hraph\SyliusPaygreenPlugin\Payum\Action\StatusAction;
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
            'payum.api_client' => '@hraph_sylius_paygreen_plugin.client.paygreen_api', // Use registered service instance
        ]);

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'username' => null,
                'api_key' => null,
                'use_sandbox_api' => false,
                'payment_type' => "CB",
                'times' => 3
            ];

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = [
                'username',
                'api_key',
                'times'
            ];

            // Set config API and save object for ApiAwareInterface
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                /** @var PaygreenApiClientInterface $paygreenApiClient */
                $paygreenApiClient = $config['payum.api_client']; // Use service

                $paygreenApiClient->setUsername($config['username']);
                $paygreenApiClient->setApiKey($config['api_key']);
                if ($config['use_sandbox_api'])
                    $paygreenApiClient->useSandboxApi($config['use_sandbox_api']);
                $paygreenApiClient->setPaymentType($config['payment_type']);
                $paygreenApiClient->setIsMultipleTimePayment(true);
                $paygreenApiClient->setMultipleTimePaymentTimes($config['times']);

                return $paygreenApiClient;
            };
        }
    }

}
