<?php


namespace Hraph\SyliusPaygreenPlugin\Client\Repository;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\ApiEntityInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Provider\PaygreenTransferProvider;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiOptions;
use Psr\Log\LoggerInterface;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;

class PaygreenApiTransferRepository  implements PaygreenApiTransferRepositoryInterface
{
    private PaygreenApiClientInterface $api;
    private PaygreenTransferProvider $transferProvider;
    private LoggerInterface $logger;

    /**
     * PaygreenApiTransferRepository constructor.
     * @param PaygreenApiFactoryInterface $factory
     * @param PaygreenTransferProvider $transferProvider
     * @param LoggerInterface $logger
     */
    public function __construct(PaygreenApiFactoryInterface $factory, PaygreenTransferProvider $transferProvider, LoggerInterface $logger)
    {
        $this->api = $factory->createNew(); // Use default config API
        $this->transferProvider = $transferProvider;
        $this->logger = $logger;
    }


    /**
     * @param string $internalId
     * @return PaygreenTransferInterface
     * @inheritDoc
     */
    public function find($internalId): ?PaygreenTransferInterface
    {
        try {
            $apiTransfer = $this->api->getPayoutTransferApi()->apiIdentifiantPayoutTransferIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $internalId)->getData();
            $transfer = $this->transferProvider->provide($internalId);

            if (null === $apiTransfer->getId()) { // Not found
                return null;
            }

            if (!empty($apiTransfer)) {
                $transfer->copyFromApiObject($apiTransfer);
            }

            return $apiTransfer;
        }
        catch (ApiException $exception) {
            $this->logger->error("PayGreen Transfers get error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Transfers get error: {$exception->getMessage()}", PaygreenException::CODE_FIND);
        }
    }

    /**
     * @return PaygreenTransferInterface[]
     * @inheritDoc
     */
    public function findAll(): array
    {
        try {
            $transfers = [];
            $apiTransfers = $this->api->getPayoutTransferApi()->apiIdentifiantPayoutTransferGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix())->getData();
            //TODO: handle pagination

            foreach ($apiTransfers as $apiTransfer){
                if (null !== $apiTransfer->getId()) {
                    $transfer = $this->transferProvider->provide($apiTransfer->getId());
                    $transfer->copyFromApiObject($apiTransfer);
                    $transfer->setShopInternalId($this->api->getUsername());
                    $transfers[] = $transfer;
                }
            }

            return $transfers;
        }
        catch (ApiException $exception) {
            $this->logger->error("PayGreen Transfers get error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Transfers get error: {$exception->getMessage()}", PaygreenException::CODE_FIND_ALL);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(ApiEntityInterface $entity): void
    {
        throw new MethodNotImplementedException("update");
    }

    /**
     * @inheritDoc
     */
    public function insert(ApiEntityInterface $entity): void
    {
        throw new MethodNotImplementedException("insert");
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
