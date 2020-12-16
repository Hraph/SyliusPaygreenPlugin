<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Factory;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShopInterface;

class PaygreenShopFactory implements PaygreenShopFactoryInterface
{
    public function create(): PaygreenShopInterface
    {
        $shop = new PaygreenShop();
        $shop->setPaiementType($this->getPaiementType());
        $shop->setAvailableMode($this->getAvailableModes());
        return $shop;
    }

    private function getAvailableModes(): array {
        return [
            "CASH",
            "RECURRING"
        ];
    }

    private function getPaiementType(): string {
        return "withoutVAD";
    }

    private function getExtras(): array {
        return [
            "redirectionAfterPaiement"=> "CONFIRMATION",
            "sendPaiementConfirmation"=> "NO_SEND",
            "sendEmailOnRefund"=> "NO_SEND",
            "sendEmailRecurring"=> "NO_SEND",
            "enableTransactionIdInMail"=> "DISABLE",
            "notifyPaiementConfirmation"=> "NO_SEND",
            "redirectMethod"=> "POST",
            "solidarityType"=> "NO",
            "enableOneClic"=> "NO",
            "acceptExpCard"=> "NO",
            "defaultTTL"=> "PT20M",
            "needShippingAddress"=> null,
            "stayInIframeConfig"=> "DONT_STAY_IN_IFRAME",
        ];
    }
}
