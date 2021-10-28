<?php


namespace Hraph\SyliusPaygreenPlugin\Provider;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenShopFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenShopRepository;

class PaygreenShopProvider
{
    private PaygreenShopRepository $shopRepository;
    private PaygreenShopFactoryInterface $shopFactory;

    public function __construct(PaygreenShopRepository $shopRepository, PaygreenShopFactoryInterface $shopFactory)
    {
        $this->shopRepository = $shopRepository;
        $this->shopFactory = $shopFactory;
    }

    public function provide(string $internalId): PaygreenShopInterface
    {
        /** @var PaygreenShopInterface|null $entity */
        $entity = $this->shopRepository->findOneBy(["internalId" => $internalId]);

        if (null === $entity) {
            $entity = $this->shopFactory->createNew();
            $entity->setInternalId($internalId);
        }

        return $entity;
    }
}
