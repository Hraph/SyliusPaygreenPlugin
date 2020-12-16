<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Factory;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransfer;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;

class PaygreenTransferFactory implements PaygreenTransferFactoryInterface
{
    public function create(): PaygreenTransferInterface
    {
        return new PaygreenTransfer();
    }
}
