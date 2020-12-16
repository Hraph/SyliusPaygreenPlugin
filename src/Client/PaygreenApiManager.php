<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Doctrine\Persistence\ObjectManager;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiTransferRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenShopFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResult;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResultInterface;

class PaygreenApiManager
{
    /**
     * @var PaygreenApiShopRepositoryInterface
     */
    private PaygreenApiShopRepositoryInterface $shopRepository;

    /**
     * @var ObjectManager
     */
    private ObjectManager $manager;

    /**
     * @var PaygreenApiTransferRepositoryInterface
     */
    private PaygreenApiTransferRepositoryInterface $transferRepository;

    /**
     * PaygreenApiManager constructor.
     * @param PaygreenApiShopRepositoryInterface $shopRepository
     * @param PaygreenApiTransferRepositoryInterface $transferRepository
     * @param ObjectManager $manager
     */
    public function __construct(PaygreenApiShopRepositoryInterface $shopRepository, PaygreenApiTransferRepositoryInterface $transferRepository, ObjectManager $manager)
    {
        $this->shopRepository = $shopRepository;
        $this->transferRepository = $transferRepository;
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
            $shops = $this->shopRepository->findAll();

            foreach ($shops as $shop){
                $this->manager->merge($shop);
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
            /** @var PaygreenShopInterface[] $shops */
            $transfers = $this->transferRepository->findAll();

            foreach ($transfers as $transfer){
                $this->manager->merge($transfer);
                ++$retrieved;
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
