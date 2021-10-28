<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


use Sylius\Component\Resource\Model\ResourceInterface;

interface PaygreenTransferInterface extends ResourceInterface, ApiEntityInterface
{
    public const STATE_PROCESSING = 'processing';

    public const STATE_SUCCEEDED = 'succeeded';

    public const STATE_FAILED = 'failed';

    public const STATE_CANCELLED = 'cancelled';

    public const STATE_NEW = 'new';

    /**
     * @return string|null
     */
    public function getShopInternalId(): ?string;

    /**
     * @param string|null $shopInternalId
     */
    public function setShopInternalId(?string $shopInternalId): void;

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
     * @return array
     */
    public function getDetails(): array;

    /**
     * @param array $details
     */
    public function setDetails(array $details): void;

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

    /**
     * @return bool
     */
    public function isWalletToWalletTransfer(): bool;
}
