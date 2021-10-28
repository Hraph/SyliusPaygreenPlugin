<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


use Doctrine\ORM\Mapping as ORM;
use Hraph\PaygreenApi\Model\ModelInterface;
use Hraph\PaygreenApi\Model\Transfer;
use Hraph\SyliusPaygreenPlugin\Types\ApiTransferStatus;

/**
 * Class PaygreenTransfer
 * @package Hraph\SyliusPaygreenPlugin\Entity
 * @ORM\MappedSuperclass
 * @ORM\Table("sylius_paygreen_transfer")
 */
class PaygreenTransfer extends ApiEntity implements PaygreenTransferInterface
{
    private const DEFAULT_CURRENCY_CODE = "EUR";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @var string|null
     * @ORM\Column(name="internal_id", type="string", nullable=true, unique=true)
     */
    protected ?string $internalId = null;

    /**
     * @var string|null
     * @ORM\Column(name="shop_internal_id", type="string", nullable=true)
     */
    protected ?string $shopInternalId = null;

    /**
     * @var string|null
     * @ORM\Column(name="status", type="string", length=10)
     */
    protected ?string $status = null;

    /**
     * @var int
     * @ORM\Column(name="amount", type="integer")
     */
    protected int $amount = 0;

    /**
     * @var string|null
     * @ORM\Column(name="currency", type="string", length=3)
     */
    protected ?string $currency = self::DEFAULT_CURRENCY_CODE;

    /**
     * @var string|null
     * @ORM\Column(name="bank_id", type="string", nullable=true)
     */
    protected ?string $bankId = null;

    /**
     * @var string|null
     * @ORM\Column(name="shop_id", type="string", nullable=true)
     */
    protected ?string $shopId = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $createdAt = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="scheduled_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $scheduledAt = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="executed_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $executedAt = null;

    /**
     * @inheritDoc
     */
    function getApiObjectType(): string
    {
        return Transfer::class;
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getInternalId(): ?string
    {
        return $this->internalId;
    }

    /**
     * @inheritDoc
     */
    public function setInternalId(?string $internalId): void
    {
        $this->internalId = $internalId;
    }

    /**
     * @inheritDoc
     */
    public function getShopInternalId(): ?string
    {
        return $this->shopInternalId;
    }

    /**
     * @inheritDoc
     */
    public function setShopInternalId(?string $shopInternalId): void
    {
        $this->shopInternalId = $shopInternalId;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @inheritDoc
     */
    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @inheritDoc
     */
    public function getBankId(): ?string
    {
        return $this->bankId;
    }

    /**
     * @inheritDoc
     */
    public function setBankId(?string $bankId): void
    {
        $this->bankId = $bankId;
    }

    /**
     * @inheritDoc
     */
    public function getShopId(): ?string
    {
        return $this->shopId;
    }

    /**
     * @inheritDoc
     */
    public function setShopId(?string $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @inheritDoc
     */
    public function getScheduledAt(): ?\DateTime
    {
        return $this->scheduledAt;
    }

    /**
     * @inheritDoc
     */
    public function setScheduledAt(?\DateTime $scheduledAt): void
    {
        $this->scheduledAt = $scheduledAt;
    }

    /**
     * @inheritDoc
     */
    public function getExecutedAt(): ?\DateTime
    {
        return $this->executedAt;
    }

    /**
     * @inheritDoc
     */
    public function setExecutedAt(?\DateTime $executedAt): void
    {
        $this->executedAt = $executedAt;
    }

    /**
     * @inheritDoc
     */
    public function copyFromApiObject(ModelInterface $transfer): void
    {
        parent::copyFromApiObject($transfer);

        $this->internalId = $transfer->getId();
        $this->status = $this->adaptApiStatusCode($transfer->getStatus());
        $this->amount = intval($transfer->getAmount());
        $this->currency = $transfer->getCurrency();
        $this->shopId = $transfer->getShopId();
        $this->bankId = $transfer->getBankId();
        $this->createdAt = $transfer->getCreatedAt();
        $this->scheduledAt = $transfer->getScheduledAt();
        $this->executedAt = $transfer->getExecutedAt();
    }

    /**
     * @inheritDoc
     */
    public function isWalletToWalletTransfer(): bool
    {
        return $this->bankId === null;
    }

    /**
     * Return adapted status code from the internal paygreen codes
     * @param string $apiStatusCode
     * @return string
     */
    private function adaptApiStatusCode(string $apiStatusCode): string
    {
        switch ($apiStatusCode) {
            case ApiTransferStatus::STATUS_SUCCEEDED:
                return self::STATE_SUCCEEDED;
            case ApiTransferStatus::STATUS_CANCELLED:
                return self::STATE_CANCELLED;
            case ApiTransferStatus::STATUS_PENDING:
                return self::STATE_PROCESSING;
            default:
                return self::STATE_FAILED;
        }
    }
}
