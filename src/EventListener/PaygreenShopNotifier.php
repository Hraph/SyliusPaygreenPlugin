<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\EventListener;


use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\ServerException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;

class PaygreenShopNotifier
{
    /**
     * @var PaygreenApiShopRepositoryInterface
     */
    private PaygreenApiShopRepositoryInterface $shopRepository;

    /**
     * PaygreenShopNotifier constructor.
     * @param PaygreenApiShopRepositoryInterface $shopRepository
     */
    public function __construct(PaygreenApiShopRepositoryInterface $shopRepository)
    {
        $this->shopRepository = $shopRepository;
    }

    /**
     * @param PaygreenShop $paygreenShop
     * @param LifecycleEventArgs $event
     * @throws PaygreenException
     */
    public function postUpdate(PaygreenShop $paygreenShop, LifecycleEventArgs $event): void{
        $this->shopRepository->update($paygreenShop);
    }

    /**
     * @param PaygreenShop $paygreenShop
     * @param LifecycleEventArgs $event
     * @throws PaygreenException
     */
    public function prePersist(PaygreenShop $paygreenShop, LifecycleEventArgs $event): void {
        $this->shopRepository->insert($paygreenShop);
    }
}
