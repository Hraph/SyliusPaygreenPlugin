<?php


namespace Hraph\SyliusPaygreenPlugin\Factory;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface PaygreenShopFactoryInterface extends FactoryInterface
{
    public function createNew(): PaygreenShopInterface;
}
