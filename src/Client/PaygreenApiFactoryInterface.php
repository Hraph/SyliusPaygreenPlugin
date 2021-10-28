<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
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
     * Set a transfer that would be used to determine the API config
     * @param PaygreenTransferInterface $transfer
     * @return mixed
     */
    public function setTransferContextForConfigResolver(PaygreenTransferInterface $transfer);

    /**
     * Return a ApiConfig depending on the payment context.
     * Return the default config unless the method is decorated.
     * Becareful: payment could be null for some a Payum request and should return the default config
     * @param PaymentInterface|null $payment
     * @return ApiConfig
     */
    public function resolveConfigFromPaymentContext(?PaymentInterface $payment): ApiConfig;

    /**
     * Return a ApiConfig depending on the transfer context.
     * Return the default config unless the method is decorated.
     * Becareful: payment could be null for some a Payum request and should return the default config
     * @param PaygreenTransferInterface|null $transfer
     * @return ApiConfig
     */
    public function resolveConfigFromTransferContext(?PaygreenTransferInterface $transfer): ApiConfig;

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

    /**
     * @return PaygreenTransferInterface|null
     */
    public function getTransferContext(): ?PaygreenTransferInterface;

    /**
     * @return bool
     */
    public function isUsingPaymentContext(): bool;
}
