<?php

namespace Hraph\SyliusPaygreenPlugin\Client\Adapter;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Types\ApiTransactionStatus;
use Hraph\SyliusPaygreenPlugin\Types\ApiTransferStatus;
use Sylius\Component\Payment\Model\PaymentInterface;

class PaygreenPaymentApiStatusAdapter
{
    public function adapt(string $status, bool $isFingerprintTransaction): string
    {
        switch ($status) {
            case ApiTransactionStatus::STATUS_REFUSED:
            case ApiTransactionStatus::STATUS_CANCELLED:
                return PaymentInterface::STATE_CANCELLED;

            case ApiTransactionStatus::STATUS_SUCCEEDED:
                if (!$isFingerprintTransaction)
                    return PaymentInterface::STATE_COMPLETED; // Succeeded when payment
                else
                    return PaymentInterface::STATE_AUTHORIZED; // Authorized when Fingerprint

            case ApiTransactionStatus::STATUS_PENDING: // Paygreen pending means no payment attempts
                return PaymentInterface::STATE_NEW;

            case ApiTransactionStatus::STATUS_REFUNDED:
                return PaymentInterface::STATE_REFUNDED;

            case ApiTransactionStatus::STATUS_EXPIRED:
                return PaymentInterface::STATE_FAILED;

            default:
                return PaymentInterface::STATE_UNKNOWN;
        }
    }
}
