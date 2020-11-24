<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;

use Hraph\PaygreenApi\Configuration;
use Hraph\PaygreenApi\HeaderSelector;
use Hraph\PaygreenApi\PaygreenApiClient as PaygreenApiClientBase;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Hraph\SyliusPaygreenPlugin\Types\ApiOptions;

class PaygreenApiClient extends PaygreenApiClientBase implements PaygreenApiClientInterface
{

    /**
     * @var string PaymentType
     */
    private string $paymentType;

    /**
     * @var bool Recurring Subscription
     */
    private bool $isMultipleTimePayment = false;

    /**
     * @var int Number of times
     */
    private int $multipleTimePaymentTimes = 3;

    /**
     * PaygreenApiClient constructor.
     * @param ApiConfig $apiConfig
     * @param ApiOptions $options
     */
    public function __construct(ApiConfig $apiConfig, ApiOptions $options)
    {
        $header = new HeaderSelector();
        $internalConfig = new Configuration();
        parent::__construct($internalConfig, $header, $host_index = 0);

        $this->setUsername($apiConfig->getUsername());
        $this->setApiKey($apiConfig->getApiKey());
        $this->useSandboxApi($options->isSandbox());
        $this->setPaymentType($options->getPaymentType());
        $this->setIsMultipleTimePayment($options->isMultiplePaymentTime());
        $this->setMultipleTimePaymentTimes($options->getMultiplePaymentTimes());
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return bool
     */
    public function isMultipleTimePayment(): bool
    {
        return $this->isMultipleTimePayment;
    }

    /**
     * @param bool $bool
     */
    public function setIsMultipleTimePayment(bool $bool): void
    {
        $this->isMultipleTimePayment = $bool;
    }

    /**
     * @return int
     */
    public function getMultipleTimePaymentTimes(): int
    {
        return $this->multipleTimePaymentTimes;
    }

    /**
     * @param int $times
     */
    public function setMultipleTimePaymentTimes(int $times): void
    {
        $this->multipleTimePaymentTimes = $times;
    }


}
