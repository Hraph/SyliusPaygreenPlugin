<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Client\Repository;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Entity\ApiEntityInterface;

interface PaygreenRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return ApiEntityInterface|null The object.
     * @throws ApiException
     */
    public function find($id): ?ApiEntityInterface;

    /**
     * Finds all objects in the repository.
     *
     * @return ApiEntityInterface[] The objects.
     * @throws ApiException
     */
    public function findAll(): array;

    /**
     * Update entity
     * @param ApiEntityInterface $entity
     * @throws ApiException
     */
    public function update(ApiEntityInterface $entity): void;

    /**
     * Insert entity
     * @param ApiEntityInterface $entity
     * @throws ApiException
     */
    public function insert(ApiEntityInterface $entity): void;

    /**
     * Delete entity
     * @param ApiEntityInterface $entity
     * @throws ApiException
     */
    public function delete(ApiEntityInterface $entity): void;
}
