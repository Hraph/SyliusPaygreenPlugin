<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Request;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Payum\Core\Request\Generic;

class GetTransferStatus extends Generic
{
    protected string $status;

    public function __construct($model)
    {
        parent::__construct($model);

        $this->markNew();
    }

    public function getValue(): string
    {
        return $this->status;
    }

    public function markSuspended(): void
    {
        $this->status = PaygreenTransferInterface::STATE_PROCESSING;
    }

    public function isSuspended(): bool
    {
        return $this->status === PaygreenTransferInterface::STATE_PROCESSING;
    }

    public function markCanceled(): void
    {
        $this->status = PaygreenTransferInterface::STATE_CANCELLED;
    }

    public function isCanceled(): bool
    {
        return $this->status === PaygreenTransferInterface::STATE_CANCELLED;
    }

    public function markPending(): void
    {
        $this->status = PaygreenTransferInterface::STATE_PROCESSING;
    }

    public function isPending(): bool
    {
        return $this->status === PaygreenTransferInterface::STATE_PROCESSING;
    }

    public function markFailed(): void
    {
        $this->status = PaygreenTransferInterface::STATE_FAILED;
    }

    public function isFailed(): bool
    {
        return $this->status === PaygreenTransferInterface::STATE_FAILED;
    }

    public function markNew(): void
    {
        $this->status = PaygreenTransferInterface::STATE_NEW;
    }

    public function isNew(): bool
    {
        return $this->status === PaygreenTransferInterface::STATE_NEW;
    }

    public function markSucceeded(): void
    {
        $this->status = PaygreenTransferInterface::STATE_SUCCEEDED;
    }

    public function isSucceeded(): bool
    {
        return $this->status === PaygreenTransferInterface::STATE_SUCCEEDED;
    }

    public function markUnknown()
    {
        $this->status = PaygreenTransferInterface::STATE_UNKNOWN;
    }

    public function isUnknown()
    {
        return $this->status === PaygreenTransferInterface::STATE_UNKNOWN;
    }
}
