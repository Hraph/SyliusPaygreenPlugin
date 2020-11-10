<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Hraph\SyliusPaygreenPlugin\Types\TransactionStatus;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Class StatusAction
 * Check the status of the payment after capture and notify
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class StatusAction extends BaseApiAwareAction implements StatusActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $paymentDetails = $payment->getDetails();
        $pid = null;

        // Transaction ID is not set in payment data. Invalid payment
        if (!isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]) && !isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID])) {
            $request->markNew();

            return;
        }

        // Multiple payment
        if (true === isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID])) {
            $pid = $paymentDetails[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID];
        }

        // One time payment
        elseif (true === isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID])) {
            $pid = $paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID];
        }

        try {
            // Search transaction
            $paymentData = $this
                ->api
                ->getPayinsTransactionApi()
                ->apiIdentifiantPayinsTransactionIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $pid);

            // Got transaction and valid status
            if (!is_null($paymentData->getData()) && !is_null($paymentData->getData()->getResult()) && !is_null($paymentData->getData()->getResult()->getStatus())) {

                switch ($paymentData->getData()->getResult()->getStatus()){
                    case TransactionStatus::STATUS_REFUSED:
                    case TransactionStatus::STATUS_CANCELLED:
                        $request->markCanceled();
                        break;

                    case TransactionStatus::STATUS_SUCCEEDED:
                        $request->markCaptured();
                        break;

                    case TransactionStatus::STATUS_PENDING:
                        $request->markPending();
                        break;

                    case TransactionStatus::STATUS_REFUNDED:
                        $request->markRefunded();
                        break;

                    case TransactionStatus::STATUS_EXPIRED:
                        $request->markExpired();
                        break;

                    default:
                        $request->markUnknown();
                        break;
                }
            }
            else throw new ApiException("Invalid API data exception. Wrong result!");
        }
        catch (\Exception $e){
            throw new ApiException(sprintf("Error with get transaction from PayGreen with %s", $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatus &&
            $request->getFirstModel() instanceof PaymentInterface
            ;
    }
}
