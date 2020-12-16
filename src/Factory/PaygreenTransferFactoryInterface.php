<?php


namespace Hraph\SyliusPaygreenPlugin\Factory;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;

interface PaygreenTransferFactoryInterface
{
    public function create(): PaygreenTransferInterface;
}
