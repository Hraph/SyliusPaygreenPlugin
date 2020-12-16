<?php


namespace Hraph\SyliusPaygreenPlugin\Factory;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface PaygreenTransferFactoryInterface extends FactoryInterface
{
    public function createNew(): PaygreenTransferInterface;
}
