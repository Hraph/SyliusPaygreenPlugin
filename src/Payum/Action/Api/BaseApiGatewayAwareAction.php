<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Psr\Log\LoggerInterface;

abstract class BaseApiGatewayAwareAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var PaygreenApiFactoryInterface|null
     */
    protected ?PaygreenApiFactoryInterface $apiFactory = null;

    /**
     * @var PaygreenApiClientInterface|null
     */
    protected ?PaygreenApiClientInterface $api = null;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * BaseApiAwareAction constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

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
