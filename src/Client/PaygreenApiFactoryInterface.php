<?php


namespace Hraph\SyliusPaygreenPlugin\Client;


use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiOptions;
use Sylius\Component\Core\Model\PaymentInterface;

interface PaygreenApiFactoryInterface
{
    /**
     * Get the API object initialized with the suitable config depending on the payment context
     * @return PaygreenApiClientInterface
     */
    public function createNew(): PaygreenApiClientInterface;

    /**
     * Set a payment that would be used to determine the API config
     * @param PaymentInterface $payment
     * @return mixed
     */
    public function setPaymentContextForConfigResolver(PaymentInterface $payment);

    /**
     * Return a ApiConfig depending on the payment context.
     * Return the default config unless the method is decorated.
     * Becareful: payment could be null for some a Payum request and sould return the default config
     * @param PaymentInterface|null $payment
     * @return ApiConfig
     */
    public function resolveConfigFromPaymentContext(?PaymentInterface $payment): ApiConfig;

    /**
     * Get options for API
     * @return ApiOptions
     */
    public function getOptions(): ApiOptions;

    /**
     * Get API auth config
     * @return ApiConfig
     */
    public function getDefaultConfig(): ApiConfig;

    /**
     * @return PaymentInterface|null
     */
    public function getPaymentContext(): ?PaymentInterface;
}
