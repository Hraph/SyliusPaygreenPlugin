<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var PaygreenApiClientInterface|mixed
     */
    protected $api;

    /**
     * Retrieve API client from config
     * @param mixed $api
     */
    public function setApi($api)
    {
        if (!$api instanceof PaygreenApiClientInterface) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . PaygreenApiClientInterface::class);
        }

        $this->api = $api;
    }
}
