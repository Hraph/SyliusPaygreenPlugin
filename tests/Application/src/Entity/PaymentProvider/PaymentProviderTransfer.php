<?php


namespace Tests\Hraph\SyliusPaygreenPlugin\App\Entity\PaymentProvider;


use Doctrine\ORM\Mapping as ORM;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransfer;

/**
 * Class PaymentProviderTransfer
 * @package App\Entity\PaymentProvider
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_provider_transfer")
 */
class PaymentProviderTransfer extends PaygreenTransfer implements PaymentProviderTransferInterface
{

}
