<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Helper;


use Sylius\Bundle\PayumBundle\Provider\PaymentDescriptionProviderInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class PaymentDescription implements PaymentDescriptionInterface
{
    /**
     * @var PaymentDescriptionProviderInterface
     */
    private PaymentDescriptionProviderInterface $paymentDescriptionProvider;

    /**
     * PaymentDescription constructor.
     * @param PaymentDescriptionProviderInterface $paymentDescriptionProvider
     */
    public function __construct(PaymentDescriptionProviderInterface $paymentDescriptionProvider)
    {
        $this->paymentDescriptionProvider = $paymentDescriptionProvider;
    }

    public function getPaymentDescription(PaymentInterface $payment, OrderInterface $order): string
    {
        return $this->paymentDescriptionProvider->getPaymentDescription($payment);
    }
}
