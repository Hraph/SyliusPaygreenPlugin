<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


use Hraph\PaygreenApi\Model\ModelInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface ApiEntityInterface extends ResourceInterface
{
    /**
     * @return string|null
     */
    public function getInternalId(): ?string;

    /**
     * @param string|null $internalId
     */
    function setInternalId(?string $internalId): void;

    /**
     * Return the class name of the API object
     * @return string
     */
    function getApiObjectType(): string;

    /**
     * Tell if data is coming from API or DB
     * @return bool
     */
    public function isFromApiData(): bool;

    /**
     * Copy data from the Client API object
     * @param ModelInterface $object
     */
    public function copyFromApiObject(ModelInterface $object): void;

    /**
     * Create an API object from Entity
     * All properties with a public getter be automatically exported to API Object
     * @return ModelInterface
     */
    public function createApiObject(): ModelInterface;
}
