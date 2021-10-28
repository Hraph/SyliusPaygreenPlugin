<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client;

use Hraph\PaygreenApi\PaygreenApiClientInterface as PaygreenApiClientInterfaceBase;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;

interface PaygreenApiClientInterface extends PaygreenApiClientInterfaceBase
{
    /**
     * @return string
     */
    public function getPaymentType(): string;

    /**
     * @param string $paymentType
     */
    public function setPaymentType(string $paymentType): void;

    /**
     * @return bool
     */
    public function isMultipleTimePayment(): bool;

    /**
     * @param bool $bool
     */
    public function setIsMultipleTimePayment(bool $bool): void;

    /**
     * @return int
     */
    public function getMultipleTimePaymentTimes(): int ;

    /**
     * @param int $times
     */
    public function setMultipleTimePaymentTimes(int $times): void;

    /**
     * @param ApiConfig $config
     */
    public function setApiConfig(ApiConfig $config): void;
}
