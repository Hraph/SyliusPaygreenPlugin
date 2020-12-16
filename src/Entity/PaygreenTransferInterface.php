<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


interface PaygreenTransferInterface extends ApiEntityInterface
{
    /**
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void;

    /**
     * @return int
     */
    public function getAmount(): int;

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void;

    /**
     * @return string|null
     */
    public function getCurrency(): ?string;

    /**
     * @param string|null $currency
     */
    public function setCurrency(?string $currency): void;

    /**
     * @return string|null
     */
    public function getBankId(): ?string;

    /**
     * @param string|null $bankId
     */
    public function setBankId(?string $bankId): void;

    /**
     * @return string|null
     */
    public function getShopId(): ?string;

    /**
     * @param string|null $shopId
     */
    public function setShopId(?string $shopId): void;

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime;

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt): void;

    /**
     * @return \DateTime|null
     */
    public function getScheduledAt(): ?\DateTime;

    /**
     * @param \DateTime|null $scheduledAt
     */
    public function setScheduledAt(?\DateTime $scheduledAt): void;

    /**
     * @return \DateTime|null
     */
    public function getExecutedAt(): ?\DateTime;

    /**
     * @param \DateTime|null $executedAt
     */
    public function setExecutedAt(?\DateTime $executedAt): void;
}
