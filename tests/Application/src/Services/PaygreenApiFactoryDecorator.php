<?php

declare(strict_types=1);

namespace Tests\Hraph\SyliusPaygreenPlugin\App\Services;


use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClient;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiClientInterface;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactory;
use Hraph\SyliusPaygreenPlugin\Client\PaygreenApiFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiOptions;
use Sylius\Component\Core\Model\PaymentInterface;

class PaygreenApiFactoryDecorator implements PaygreenApiFactoryInterface
{
    /**
     * @var PaygreenApiFactory|null
     */
    private ?PaygreenApiFactory $decorator;

    /**
     * PaygreenApiFactoryDecorator constructor.
     * @param PaygreenApiFactory|null $decorator
     */
    public function __construct(?PaygreenApiFactory $decorator)
    {
        $this->decorator = $decorator;
    }

    public function createNew(): PaygreenApiClientInterface
    {
        return new PaygreenApiClient($this->resolveConfigFromPaymentContext($this->getPaymentContext()), $this->getOptions());
    }

    public function getOptions(): ApiOptions
    {
        return $this->decorator->getOptions();
    }

    public function getDefaultConfig(): ApiConfig
    {
        return $this->decorator->getDefaultConfig();
    }

    public function getPaymentContext(): ?PaymentInterface
    {
        return $this->decorator->getPaymentContext();
    }

    public function setPaymentContextForConfigResolver(PaymentInterface $payment)
    {
        return $this->decorator->setPaymentContextForConfigResolver($payment);
    }

    public function resolveConfigFromPaymentContext(?PaymentInterface $payment): ApiConfig
    {
//        return new ApiConfig("pierre", "paul");
        return $this->decorator->resolveConfigFromPaymentContext($payment);
    }
}
