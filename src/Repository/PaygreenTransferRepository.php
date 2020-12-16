<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Repository;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use SyliusLabs\AssociationHydrator\AssociationHydrator;

class PaygreenTransferRepository extends EntityRepository
{
    /** @var AssociationHydrator */
    private AssociationHydrator $associationHydrator;

    public function __construct(EntityManager $entityManager, Mapping\ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->associationHydrator = new AssociationHydrator($entityManager, $class);
    }
}
