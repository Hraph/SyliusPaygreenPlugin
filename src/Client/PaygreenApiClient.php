<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;

use Hraph\PaygreenApi\Configuration;
use Hraph\PaygreenApi\HeaderSelector;
use Hraph\PaygreenApi\PaygreenApiClient as PaygreenApiClientBase;

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
     * @param string $username
     * @param string $apiKey
     * @param bool $sandbox
     * @param string $paymentType
     */
    public function __construct(string $username, string $apiKey, bool $sandbox, string $paymentType)
    {
        $header = new HeaderSelector();
        $config = new Configuration();
        parent::__construct($config, $header, $host_index = 0);

        $this->setUsername($username);
        $this->setApiKey($apiKey);
        $this->useSandboxApi($sandbox);
        $this->setPaymentType($paymentType);
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
