<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


interface PaygreenShopInterface extends ApiEntityInterface
{
    public function getId(): ?string;

    public function setId($id): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getUrl(): ?string;

    public function setUrl(?string $url): void;

    public function getPrivateKey(): ?string;

    public function setPrivateKey(?string $privateKey): void;

    public function getAvailableMode(): ?array ;

    public function setAvailableMode(?array $availableMode): void;

    public function getBusinessIdentifier(): ?string;

    public function setBusinessIdentifier(?string $businessIdentifier): void;

    public function getCompanyType(): ?string;

    public function setCompanyType(?string $companyType): void;

    public function getDescription(): ?string;

    public function setDescription(?string $description): void;

    public function getActivate(): bool ;

    public function setActivate(bool $activate): void;

    public function getValidatedAt(): ?\DateTime;

    public function setValidatedAt(?\DateTime $validated): void;

    public function getCreatedAt(): ?\DateTime;

    public function setCreatedAt(?\DateTime $created): void;

    public function getPaiementType(): ?string;

    public function setPaiementType(?string $paiementType): void;

    public function getExtra(): ?array;

    public function setExtra(?array $extra): void;
}
