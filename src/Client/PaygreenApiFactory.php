<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiOptions;
use Sylius\Component\Core\Model\PaymentInterface;

class PaygreenApiFactory implements PaygreenApiFactoryInterface
{
    private ApiConfig $defaultConfig;
    private ApiOptions $options;
    private ?PaymentInterface $paymentContext = null;
    private ?PaygreenTransferInterface $transferContext = null;
    private bool $usingPaymentContext = true;

    /**
     * PaygreenApiFactory constructor.
     * @param ApiConfig $defaultConfig
     * @param ApiOptions $options
     */
    public function __construct(ApiConfig $defaultConfig, ApiOptions $options)
    {
        $this->defaultConfig = $defaultConfig;
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function createNew(): PaygreenApiClientInterface
    {
        return new PaygreenApiClient($this->usingPaymentContext ?
            $this->resolveConfigFromPaymentContext($this->paymentContext) :
            $this->resolveConfigFromTransferContext($this->transferContext), $this->options);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentContextForConfigResolver (PaymentInterface $payment): void
    {
        $this->paymentContext = $payment;
        $this->usingPaymentContext = true;
    }

    /**
     * @inheritDoc
     */
    public function setTransferContextForConfigResolver(PaygreenTransferInterface $transfer): void
    {
        $this->transferContext = $transfer;
        $this->usingPaymentContext = false;
    }

    /**
     * @inheritDoc
     */
    public function resolveConfigFromPaymentContext(?PaymentInterface $payment): ApiConfig
    {
        return $this->defaultConfig;
    }

    /**
     * @inheritDoc
     */
    public function resolveConfigFromTransferContext(?PaygreenTransferInterface $transfer): ApiConfig
    {
        return $this->defaultConfig;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultConfig(): ApiConfig
    {
        return $this->defaultConfig;
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): ApiOptions
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentContext(): ?PaymentInterface
    {
        return $this->paymentContext;
    }

    /**
     * @inheritDoc
     */
    public function getTransferContext(): ?PaygreenTransferInterface
    {
        return $this->transferContext;
    }

    /**
     * @inheritDoc
     */
    public function isUsingPaymentContext(): bool
    {
        return $this->usingPaymentContext;
    }
}
