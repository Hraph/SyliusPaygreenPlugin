<?php


namespace Tests\Hraph\SyliusPaygreenPlugin\App\Entity\PaymentProvider;


use Doctrine\ORM\Mapping as ORM;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;

/**
 * Class Shop
 * @package App\Entity\PaymentProvider
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_provider_shop")
 */
class PaymentProviderShop extends PaygreenShop implements PaymentProviderShopInterface
{

}
