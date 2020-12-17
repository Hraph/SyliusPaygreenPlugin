<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\EventListener;


use Doctrine\Persistence\Event\LifecycleEventArgs;
use Hraph\SyliusPaygreenPlugin\Client\Repository\PaygreenApiShopRepositoryInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Sylius\Component\Resource\Exception\UpdateHandlingException;

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
     * @throws UpdateHandlingException
     */
    public function postUpdate(PaygreenShop $paygreenShop, LifecycleEventArgs $event): void{
        try {
            $this->shopRepository->update($paygreenShop);
        }
        catch (PaygreenException $exception) {
            throw new UpdateHandlingException($exception->getMessage(), 'paygreen.api_update_error'); // Handled by resource controller
        }
    }

    /**
     * @param PaygreenShop $paygreenShop
     * @param LifecycleEventArgs $event
     * @throws UpdateHandlingException
     */
    public function prePersist(PaygreenShop $paygreenShop, LifecycleEventArgs $event): void {
        try {
            $this->shopRepository->insert($paygreenShop);
        }
        catch (PaygreenException $exception) {
            throw new UpdateHandlingException($exception->getMessage(), 'paygreen.api_insert_error'); // Handled by resource controller
        }
    }
}
