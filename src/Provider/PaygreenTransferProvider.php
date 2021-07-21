<?php


namespace Hraph\SyliusPaygreenPlugin\Provider;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenTransferFactoryInterface;
use Hraph\SyliusPaygreenPlugin\Repository\PaygreenTransferRepository;

class PaygreenTransferProvider
{
    private PaygreenTransferRepository $transferRepository;
    private PaygreenTransferFactoryInterface $transferFactory;

    public function __construct(PaygreenTransferRepository $transferRepository, PaygreenTransferFactoryInterface $transferFactory)
    {
        $this->transferRepository = $transferRepository;
        $this->transferFactory = $transferFactory;
    }

    public function provide(string $internalId): PaygreenTransferInterface
    {
        /** @var PaygreenTransferInterface|null $entity */
        $entity = $this->transferRepository->findOneBy(["internalId" => $internalId]);

        if (null === $entity) {
            $entity = $this->transferFactory->createNew();
        }

        return $entity;
    }
}
