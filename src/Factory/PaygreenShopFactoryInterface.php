<?php


namespace Hraph\SyliusPaygreenPlugin\Factory;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;

interface PaygreenShopFactoryInterface
{
    public function create(): PaygreenShopInterface;
}
