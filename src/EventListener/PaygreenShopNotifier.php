<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\EventListener;


use Doctrine\Persistence\Event\LifecycleEventArgs;
use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;

class PaygreenShopNotifier
{
    /**
     * @var PaygreenShopRepositoryInterface
     */
    private PaygreenShopRepositoryInterface $shopRepository;

    /**
     * PaygreenShopNotifier constructor.
     * @param PaygreenShopRepositoryInterface $shopRepository
     */
    public function __construct(PaygreenShopRepositoryInterface $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    public function postUpdate(PaygreenShop $paygreenShop, LifecycleEventArgs $event){
        try {
            $this->shopRepository->update($paygreenShop);
        } catch (ApiException $e) {
            //TODO LOG
        }
    }

}
