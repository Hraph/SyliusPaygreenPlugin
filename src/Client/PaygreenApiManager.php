<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Doctrine\ORM\EntityManagerInterface;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiTransferRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenShopRepository;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenTransferRepository;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResult;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResultInterface;

class PaygreenApiManager
{
    private PaygreenApiShopRepositoryInterface $apiShopRepository;
    private PaygreenApiTransferRepositoryInterface $apiTransferRepository;
    private EntityManagerInterface $manager;

    /**
     * PaygreenApiManager constructor.
     * @param PaygreenApiShopRepositoryInterface $apiShopRepository
     * @param PaygreenApiTransferRepositoryInterface $apiTransferRepository
     * @param EntityManagerInterface $manager
     */
    public function __construct(
                                PaygreenApiShopRepositoryInterface $apiShopRepository,
                                PaygreenApiTransferRepositoryInterface $apiTransferRepository,
                                EntityManagerInterface $manager)
    {
        $this->apiShopRepository = $apiShopRepository;
        $this->apiTransferRepository = $apiTransferRepository;
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
            /** @var PaygreenShopInterface[] $shops */
            $transfers = $this->apiTransferRepository->findAll();

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
