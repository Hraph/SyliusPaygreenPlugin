<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


use Doctrine\ORM\Mapping as ORM;
use Hraph\PaygreenApi\Model\ModelInterface;
use Hraph\PaygreenApi\Model\Shop;

/**
 * Class PaygreenShop
 * @package Hraph\SyliusPaygreenPlugin\Entity
 * @ORM\MappedSuperclass
 * @ORM\Table("sylius_paygreen_shop")
 */
class PaygreenShop extends ApiEntity implements PaygreenShopInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @var string|null
     * @ORM\Column(name="internal_id", type="string", nullable=false, unique=true)
     */
    protected ?string $internalId = null;

    /**
     * @var string|null
     * @ORM\Column(name="private_key", type="string", nullable=true)
     */
    protected ?string $privateKey = null;

    /**
     * @var string|null
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected ?string $name = null;

    /**
     * @var string|null
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    protected ?string $url = null;

    /**
     * @var string[]|null
     * @ORM\Column(name="available_mode", type="simple_array", nullable=true)
     */
    protected ?array $availableMode = [];

    /**
     * @var string|null
     * @ORM\Column(name="business_identifier", type="string", nullable=true)
     */
    protected ?string $businessIdentifier = null;

    /**
     * @var string|null
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected ?string $description = null;

    /**
     * @var string|null
     * @ORM\Column(name="company_type", type="string", nullable=true)
     */
    protected ?string $companyType = null;

    /**
     * @var string|null
     * @ORM\Column(name="paiement_type", type="string", nullable=true)
     */
    protected ?string $paiementType = null;

    /**
     * @var bool
     * @ORM\Column(name="activate", type="boolean")
     */
    protected bool $activate = false;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $createdAt = null;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="validated_at", type="datetime", nullable=true)
     */
    protected ?\DateTime $validatedAt = null;

    /**
     * @var array|null
     */
    protected ?array $extra = [];


    /**
     * @inheritDoc
     */
    function getApiObjectType(): string
    {
        return Shop::class;
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
     * @return string|null
     */
    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    /**
     * @param string|null $privateKey
     */
    public function setPrivateKey(?string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return array|string[]|null
     */
    public function getAvailableMode(): ?array
    {
        return $this->availableMode;
    }

    /**
     * @param array|null $availableMode
     */
    public function setAvailableMode(?array $availableMode): void
    {
        $this->availableMode = $availableMode;
    }

    /**
     * @return string|null
     */
    public function getBusinessIdentifier(): ?string
    {
        return $this->businessIdentifier;
    }

    /**
     * @param string|null $businessIdentifier
     */
    public function setBusinessIdentifier(?string $businessIdentifier): void
    {
        $this->businessIdentifier = $businessIdentifier;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getCompanyType(): ?string
    {
        return $this->companyType;
    }

    /**
     * @param string|null $companyType
     */
    public function setCompanyType(?string $companyType): void
    {
        $this->companyType = $companyType;
    }

    /**
     * @return string|null
     */
    public function getPaiementType(): ?string
    {
        return $this->paiementType;
    }

    /**
     * @param string|null $paiementType
     */
    public function setPaiementType(?string $paiementType): void
    {
        $this->paiementType = $paiementType;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->activate;
    }

    /**
     * @param bool $activate
     */
    public function setActivate(bool $activate): void
    {
        $this->activate = $activate;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidatedAt(): ?\DateTime
    {
        return $this->validatedAt;
    }

    /**
     * @param \DateTime|null $validatedAt
     */
    public function setValidatedAt(?\DateTime $validatedAt): void
    {
        $this->validatedAt = $validatedAt;
    }

    /**
     * @return array|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }

    /**
     * @param array|null $extra
     */
    public function setExtra(?array $extra): void
    {
        $this->extra = $extra;
    }

    /**
     * @return bool
     */
    public function getActivate(): bool
    {
        return $this->activate;
    }

    /**
     * @inheritDoc
     */
    public function copyFromApiObject(ModelInterface $shop): void
    {
        parent::copyFromApiObject($shop);

        $this->id = $shop->getId();
        $this->activate = $shop->getActivate();
        $this->name = $shop->getName();
        $this->extra = $shop->getExtra();
        $this->description = $shop->getDescription();
        $this->url = $shop->getUrl();
        $this->availableMode = $shop->getAvailableMode();
        $this->businessIdentifier = $shop->getBusinessIdentifier();
        $this->paiementType = $shop->getPaiementType();
        $this->privateKey = $shop->getPrivateKey();
        $this->companyType = $shop->getCompanyType();
        $this->createdAt = $shop->getCreatedAt();
        $this->validatedAt = $shop->getValidatedAt();
    }

}
