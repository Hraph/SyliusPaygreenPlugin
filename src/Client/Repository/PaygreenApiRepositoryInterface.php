<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client\Repository;

use Hraph\SyliusPaygreenPlugin\Entity\ApiEntityInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Types\ApiConfig;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

interface PaygreenApiRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $internalId The identifier.
     *
     * @return ApiEntityInterface|null The object.
     * @throws PaygreenException|MethodNotImplementedException
     */
    public function find($internalId): ?ApiEntityInterface;

    /**
     * Finds all objects in the repository.
     *
     * @return ApiEntityInterface[] The objects.
     * @throws PaygreenException|MethodNotImplementedException
     */
    public function findAll(): array;

    /**
     * Update entity
     * @param ApiEntityInterface $entity
     * @throws PaygreenException|MethodNotImplementedException
     */
    public function update(ApiEntityInterface $entity): void;

    /**
     * Insert entity
     * @param ApiEntityInterface $entity
     * @throws PaygreenException|MethodNotImplementedException
     */
    public function insert(ApiEntityInterface $entity): void;

    /**
     * Delete entity
     * @param ApiEntityInterface $entity
     * @throws PaygreenException|MethodNotImplementedException
     */
    public function delete(ApiEntityInterface $entity): void;

    /**
     * Change the context of the API by passing new config
     * @param ApiConfig $config
     */
    public function updateApiContext(ApiConfig $config): void;
}
