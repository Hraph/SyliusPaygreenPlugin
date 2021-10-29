<?php

namespace Hraph\SyliusPaygreenPlugin\Client\Adapter;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Types\ApiTransferStatus;

class PaygreenTransferApiStatusAdapter
{
    public function adapt(string $status): string
    {
        switch ($status) {
            case ApiTransferStatus::STATUS_SUCCEEDED:
                return PaygreenTransferInterface::STATE_COMPLETED;
            case ApiTransferStatus::STATUS_CANCELLED:
                return PaygreenTransferInterface::STATE_CANCELLED;
            case ApiTransferStatus::STATUS_PENDING:
                return PaygreenTransferInterface::STATE_PROCESSING;
            default:
                return PaygreenTransferInterface::STATE_FAILED;
        }
    }
}
