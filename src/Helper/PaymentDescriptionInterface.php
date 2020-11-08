<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Helper;


use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentDescriptionInterface
{
    public function getPaymentDescription(
        PaymentInterface $payment,
        OrderInterface $order
    ): string;
}
