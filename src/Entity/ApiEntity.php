<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Entity;


use Hraph\PaygreenApi\Model\ModelInterface;
use Symfony\Component\Asset\Exception\InvalidArgumentException;

abstract class ApiEntity implements ApiEntityInterface
{
    private bool $isFromApiData = false;

    /**
     * @inheritDoc
     */
    public function isFromApiData(): bool
    {
        return $this->isFromApiData;
    }

    /**
     * @inheritDoc
     */
    public function copyFromApiObject(ModelInterface $object): void
    {
        $this->isFromApiData = true;
    }

    /**
     * Map internalId with api object id attribute
     * @inheritDoc
     */
    function getCustomApiObjectAttributeMapping(): array
    {
        return [
            "internalId" => "id"
        ];
    }

    /**
     * @inheritDoc
     */
    public function createApiObject(): ModelInterface
    {
        $apiObjectType = $this->getApiObjectType();
        $customAttributeMapping = $this->getCustomApiObjectAttributeMapping();
        $apiData = [];

        if (!method_exists($apiObjectType, 'attributeMap'))
            throw new InvalidArgumentException("Invalid API object type");

        // Get API object properties
        $attributeMap = call_user_func($apiObjectType . '::attributeMap');

        foreach ($attributeMap as $privateAttrName => $publicAttrName) {
            if ($customPublicAttrName = array_search($publicAttrName, $customAttributeMapping, true)) {
                $publicAttrName = $customPublicAttrName;
            }

            $getterName = 'get' . ucfirst($publicAttrName);

            // Entity property has getter for this API property
            if (method_exists($this, $getterName)) {
                $apiData[$privateAttrName] = $this->$getterName(); // Save entity data
            }
        }
        return new $apiObjectType($apiData); // Crate API Object with entity data
    }


}
