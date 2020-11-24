<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var PaygreenApiFactoryInterface|mixed
     */
    protected $apiFactory;

    /**
     * @var PaygreenApiClientInterface|null
     */
    protected ?PaygreenApiClientInterface $api;

    /**
     * Retrieve API client from config
     * @param mixed $apiFactory
     */
    public function setApi($apiFactory)
    {
        if (!$apiFactory instanceof PaygreenApiFactoryInterface) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . PaygreenApiFactoryInterface::class);
        }

        $this->apiFactory = $apiFactory;
        $this->api = $apiFactory->createNew(); // This will create a client depending on config and options provided by gateway and previous Payum request
    }
}
