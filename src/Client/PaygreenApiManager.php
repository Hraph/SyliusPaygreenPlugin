<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Doctrine\Persistence\ObjectManager;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResult;
use Hraph\SyliusPaygreenPlugin\Types\ApiTaskResultInterface;

class PaygreenApiManager
{
    /**
     * @var PaygreenShopRepositoryInterface
     */
    private PaygreenShopRepositoryInterface $shopRepository;

    /**
     * @var ObjectManager
     */
    private ObjectManager $manager;

    /**
     * PaygreenApiManager constructor.
     * @param PaygreenShopRepositoryInterface $shopRepository
     * @param ObjectManager $manager
     */
    public function __construct(PaygreenShopRepositoryInterface $shopRepository, ObjectManager $manager)
    {
        $this->shopRepository = $shopRepository;
        $this->manager = $manager;
    }

    /**
     * Synchronize DB from API
     * @return ApiTaskResultInterface
     */
    public function synchronizeShops(): ApiTaskResultInterface {
        $result = new ApiTaskResult();
        $retrieved = 0;

        try {
            /** @var PaygreenShop[] $shops */
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
}
