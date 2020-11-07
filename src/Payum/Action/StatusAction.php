<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Hraph\SyliusPaygreenPlugin\Types\TransactionStatus;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Class StatusAction
 * Check the status of the payment after capture and notify
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class StatusAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
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

        // Transaction ID is not set. Invalid payment
        if (!isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID])) {
            $request->markNew();

            return;
        }

        try {
            // Search transaction
            $paymentData = $this
                ->api
                ->getPayinsTransactionApi()
                ->apiIdentifiantPayinsTransactionIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]);

            // Got transaction and valid status
            if (!is_null($paymentData->getData()) && !is_null($paymentData->getData()->getResult()) && !is_null($paymentData->getData()->getResult()->getStatus())) {

                switch ($paymentData->getData()->getResult()->getStatus()){
                    case TransactionStatus::STATUS_REFUSED:
                        $request->markRefused();
                        break;

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

                    default:
                        $request->markUnknown();
                        break;
                }
            }
            else throw new ApiException("Invalid API data exception. Wrong result!");
        }
        catch (ApiException $e){
            //TODO handle exception
            echo 'Exception when calling API: ', $e->getMessage(), PHP_EOL;
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
