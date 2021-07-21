<?php


namespace Hraph\SyliusPaygreenPlugin\Client\Repository;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\ApiEntityInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenTransferFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;

class PaygreenApiTransferRepository  implements PaygreenApiTransferRepositoryInterface
{
    private PaygreenApiClientInterface $api;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var PaygreenTransferFactoryInterface
     */
    private PaygreenTransferFactoryInterface $transferFactory;

    /**
     * PaygreenShopRepository constructor.
     * @param PaygreenApiFactoryInterface $factory
     * @param PaygreenTransferFactoryInterface $transferFactory
     * @param LoggerInterface $logger
     */
    public function __construct(PaygreenApiFactoryInterface $factory, PaygreenTransferFactoryInterface $transferFactory, LoggerInterface $logger)
    {
        $this->api = $factory->createNew(); // Use default config API
        $this->transferFactory = $transferFactory;
        $this->logger = $logger;
    }


    /**
     * @param string $id
     * @return PaygreenTransferInterface
     * @inheritDoc
     */
    public function find($id): ?PaygreenTransferInterface
    {
        try {
            $transfer = $this->transferFactory->createNew();
            $apiTransfer = $this->api->getPayoutTransferApi()->apiIdentifiantPayoutTransferIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $id)->getData();
            $transfer->copyFromApiObject($apiTransfer);
            return $transfer;
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
            $transfer = [];
            $apiTransfers = $this->api->getPayoutTransferApi()->apiIdentifiantPayoutTransferGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix())->getData();

            foreach ($apiTransfers as $apiTransfer){
                $transfer = $this->transferFactory->createNew();
                $transfer->copyFromApiObject($apiTransfer);
                $transfer[] = $transfer;
            }

            return $transfer;
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
}
