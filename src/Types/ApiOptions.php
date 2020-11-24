<?php


namespace Hraph\SyliusPaygreenPlugin\Types;


class ApiOptions
{
    private bool $sandbox;

    private string $paymentType;

    private bool $isMultiplePaymentTime;

    private int $multiplePaymentTimes;

    /**
     * ApiOptions constructor.
     * @param bool $sandbox
     * @param string $paymentType
     * @param bool $multiplePaymentTime
     * @param int $multiplePaymentTimes
     */
    public function __construct(bool $sandbox = false, string $paymentType = "CB", bool $multiplePaymentTime = false, int $multiplePaymentTimes = 3)
    {
        $this->sandbox = $sandbox;
        $this->paymentType = $paymentType;
        $this->isMultiplePaymentTime = $multiplePaymentTime;
        $this->multiplePaymentTimes = $multiplePaymentTimes;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandbox(bool $sandbox): void
    {
        $this->sandbox = $sandbox;
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
    public function isMultiplePaymentTime(): bool
    {
        return $this->isMultiplePaymentTime;
    }

    /**
     * @param bool $isMultiplePaymentTime
     */
    public function setIsMultiplePaymentTime(bool $isMultiplePaymentTime): void
    {
        $this->isMultiplePaymentTime = $isMultiplePaymentTime;
    }

    /**
     * @return int
     */
    public function getMultiplePaymentTimes(): int
    {
        return $this->multiplePaymentTimes;
    }

    /**
     * @param int $multiplePaymentTimes
     */
    public function setMultiplePaymentTimes(int $multiplePaymentTimes): void
    {
        $this->multiplePaymentTimes = $multiplePaymentTimes;
    }


}
