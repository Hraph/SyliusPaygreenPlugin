<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiOptions;
use Sylius\Component\Core\Model\PaymentInterface;

class PaygreenApiFactory implements PaygreenApiFactoryInterface
{
    /**
     * @var ApiConfig
     */
    private ApiConfig $defaultConfig;

    /**
     * @var ApiOptions
     */
    private ApiOptions $options;

    /**
     * @var PaymentInterface|null
     */
    private ?PaymentInterface $paymentContext = null;

    /**
     * @var PaygreenTransferInterface|null
     */
    private ?PaygreenTransferInterface $transferContext = null;

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
        return new PaygreenApiClient($this->resolveConfigFromPaymentContext($this->paymentContext), $this->options);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentContextForConfigResolver (PaymentInterface $payment): void
    {
        $this->paymentContext = $payment;
    }

    /**
     * @inheritDoc
     */
    public function setTransferContextForConfigResolver(PaygreenTransferInterface $transfer): void
    {
        $this->transferContext = $transfer;
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
}
