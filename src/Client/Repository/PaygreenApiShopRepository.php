<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client\Repository;

use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Shop;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\ApiEntityInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Provider\PaygreenShopProvider;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Psr\Log\LoggerInterface;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;

class PaygreenApiShopRepository implements PaygreenApiShopRepositoryInterface
{
    private PaygreenApiClientInterface $api;
    private PaygreenShopProvider $shopProvider;
    private LoggerInterface $logger;

    /**
     * PaygreenShopRepository constructor.
     * @param PaygreenApiFactoryInterface $factory
     * @param PaygreenShopProvider $shopProvider
     * @param LoggerInterface $logger
     */
    public function __construct(PaygreenApiFactoryInterface $factory, PaygreenShopProvider $shopProvider, LoggerInterface $logger)
    {
        $this->api = $factory->createNew(); // Use default config API
        $this->shopProvider = $shopProvider;
        $this->logger = $logger;
    }


    /**
     * @param string $internalId
     * @return PaygreenShopInterface
     * @inheritDoc
     */
    public function find($internalId): ?PaygreenShopInterface
    {
        try {
            $apiShop = $this->api->getShopApi()->apiIdentifiantShopShopIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $internalId)->getData();
            $shop = $this->shopProvider->provide($internalId);
            if (null === $shop->getId()) { // Not found
                return null;
            }

            if (isset($apiShop)) {
                $shop->copyFromApiObject($apiShop);
            }
            return $shop;
        }
        catch (ApiException $exception) {
            $this->logger->error("PayGreen Shop get error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Shop get error: {$exception->getMessage()}", PaygreenException::CODE_FIND);
        }
    }

    /**
     * @return PaygreenShopInterface[]
     * @inheritDoc
     */
    public function findAll(): array
    {
        try {
            $shops = [];
            $apiShops = $this->api->getShopsApi()->apiIdentifiantShopsGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix())->getData();

            foreach ($apiShops as $apiShop){
                if (null !== $apiShop->getId()) {
                    $shop = $this->shopProvider->provide($apiShop->getId());
                    $shop->copyFromApiObject($apiShop);
                    $shops[] = $shop;
                }
            }

            return $shops;
        }
        catch (ApiException $exception) {
            $this->logger->error("PayGreen Shops get error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Shops get error: {$exception->getMessage()}", PaygreenException::CODE_FIND_ALL);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(ApiEntityInterface $entity): void
    {
        try {
            if ($entity->isFromApiData()) {
                return;
            }

            /** @var Shop $apiObject */
            $apiObject = $entity->createApiObject();
            $result = $this->api->getShopApi()->apiIdentifiantShopShopIdPut($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $entity->getInternalId(), $apiObject);

            if (!$result->getSuccess()) {
                throw new ApiException("Update failed: {$result->getMessage()}");
            }
        }
        catch (ApiException $exception) {
            $this->logger->error("PayGreen Shop update error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Shop update error: {$exception->getMessage()}", PaygreenException::CODE_UPDATE);
        }
    }

    /**
     * @inheritDoc
     */
    public function insert(ApiEntityInterface $entity): void
    {
        try {
            if ($entity->isFromApiData()) {
                return;
            }

            /** @var Shop $apiObject */
            $apiObject = $entity->createApiObject();
            $result = $this->api->getShopApi()->apiIdentifiantShopPost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $apiObject);

            if (!$result->getSuccess()) {
                throw new ApiException("Insert failed: {$result->getMessage()}");
            }
            elseif (null !== $result->getData()) {
                $entity->copyFromApiObject($result->getData());
            }
            else {
                throw new ApiException("Wrong data");
            }
        }
        catch (ApiException $exception) {
            $this->logger->error("PayGreen Shop insert error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Shop insert error: {$exception->getMessage()}", PaygreenException::CODE_INSERT);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(ApiEntityInterface $entity): void
    {
        throw new MethodNotImplementedException("delete");
    }

    /**
     * @inheritDoc
     */
    public function updateApiContext(ApiConfig $config): void
    {
        $this->api->setApiConfig($config);
    }
}
