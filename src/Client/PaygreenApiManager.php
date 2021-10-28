<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Doctrine\ORM\EntityManagerInterface;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiTransferRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenShopRepository;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenTransferRepository;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResult;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResultInterface;

class PaygreenApiManager
{
    private PaygreenApiShopRepositoryInterface $apiShopRepository;
    private PaygreenApiTransferRepositoryInterface $apiTransferRepository;
    private PaygreenShopRepository $shopRepository;
    private ApiConfig $paygreenDefaultConfig;
    private EntityManagerInterface $manager;

    public function __construct(
                                PaygreenApiShopRepositoryInterface $apiShopRepository,
                                PaygreenApiTransferRepositoryInterface $apiTransferRepository,
                                PaygreenShopRepository $shopRepository,
                                ApiConfig $paygreenDefaultConfig,
                                EntityManagerInterface $manager)
    {
        $this->apiShopRepository = $apiShopRepository;
        $this->apiTransferRepository = $apiTransferRepository;
        $this->shopRepository = $shopRepository;
        $this->paygreenDefaultConfig = $paygreenDefaultConfig;
        $this->manager = $manager;
    }

    /**
     * Synchronize Shops from API
     * @return ApiTaskResultInterface
     */
    public function synchronizeShops(): ApiTaskResultInterface {
        $result = new ApiTaskResult();
        $retrieved = 0;

        try {
            /** @var PaygreenShopInterface[] $shops */
            $shops = $this->apiShopRepository->findAll();

            foreach ($shops as $shop){
                $this->manager->persist($shop);
                ++$retrieved;
            }

            $this->manager->flush();
        }
        catch (\Exception $e){
            $result->setMessage("Unable to synchronize Shops: {$e->getMessage()}");
            $result->setIsSuccess(false);
            $result->setData($e);
        } finally {
            $result->setSuccessCount($retrieved);
            return $result;
        }
    }

    /**
     * Synchronize Transfers from API
     * @return ApiTaskResultInterface
     */
    public function synchronizeTransfers(): ApiTaskResultInterface {
        $result = new ApiTaskResult();
        $retrieved = 0;

        try {
            /** @var PaygreenShop[] $shops */
            $shops = $this->shopRepository->findAll();

            if (empty($shops)) { // Store context from default store only
                $contextsConfigs = [$this->paygreenDefaultConfig];
            }
            else { // Store a context for each shop
                $contextsConfigs = [];
                foreach ($shops as $shop) {
                    if (null !== $shop->getInternalId() && null !== $shop->getPrivateKey()) {
                        $contextsConfigs[] = new ApiConfig($shop->getInternalId(), $shop->getPrivateKey());
                    }
                }
            }

            // Get transfers from each shop (each api contexts)
            foreach ($contextsConfigs as $contextConfig) {
                $this->apiTransferRepository->updateApiContext($contextConfig); // Replace context for next query

                /** @var PaygreenTransferInterface[] $shops */
                $transfers = $this->apiTransferRepository->findAll();

                foreach ($transfers as $transfer){
                    $this->manager->persist($transfer);
                    ++$retrieved;
                }
            }
            $this->manager->flush();
        }
        catch (\Exception $e){
            $result->setMessage("Unable to synchronize Transfers: {$e->getMessage()}");
            $result->setIsSuccess(false);
            $result->setData($e);
        } finally {
            $result->setSuccessCount($retrieved);
            return $result;
        }
    }
}
