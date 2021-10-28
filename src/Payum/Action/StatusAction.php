<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Hraph\SyliusPaygreenPlugin\Types\ApiTransactionStatus;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Class StatusAction is called by Payum controller
 * Check the status of the payment after capture and notify
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class StatusAction extends BaseApiGatewayAwareAction implements ActionInterface
{
    /**
     * @var GetHttpRequest
     */
    private GetHttpRequest $getHttpRequest;

    /**
     * StatusAction constructor.
     * @param GetHttpRequest $getHttpRequest
     * @param LoggerInterface $logger
     */
    public function __construct(GetHttpRequest $getHttpRequest, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->getHttpRequest = $getHttpRequest;
    }

    /**
     * @inheritDoc
     * @param GetStatus $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->gateway->execute($this->getHttpRequest); // Get POST/GET data and query from request

        /** @var PaymentInterface $payment */
        $payment = $request->getModel();
        $paymentDetails = $payment->getDetails();
        $pid = null;
        $isFingerprintTransaction = false;

        // Multiple payment
        if (true === isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID])) {
            $pid = $paymentDetails[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID];
        }
        // One time payment
        elseif (true === isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID])) {
            $pid = $paymentDetails[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID];
        }
        // Fringerprint transaction
        elseif (true === isset($paymentDetails[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID])) {
            $pid = $paymentDetails[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID];
            $isFingerprintTransaction = true;
        }
        // Transaction ID is not set in payment data. Invalid payment
        else {
            $request->markNew();
            return;
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
                    case ApiTransactionStatus::STATUS_REFUSED:
                    case ApiTransactionStatus::STATUS_CANCELLED:
                        $request->markCanceled();
                        break;

                    case ApiTransactionStatus::STATUS_SUCCEEDED:
                        if (!$isFingerprintTransaction)
                            $request->markCaptured(); // Succeeded when payment
                        else
                            $request->markAuthorized(); // Authorized when Fingerprint
                        break;

                    case ApiTransactionStatus::STATUS_PENDING: // Paygreen pending means no payment attempts
                        $request->markNew();
                        break;

                    case ApiTransactionStatus::STATUS_REFUNDED:
                        $request->markRefunded();
                        break;

                    case ApiTransactionStatus::STATUS_EXPIRED:
                        $request->markExpired();
                        break;

                    default:
                        $request->markUnknown();
                        break;
                }
            }
            else throw new ApiException("Invalid API transaction data.");
        }
        catch (ApiException $exception){
            $this->logger->error("PayGreen Status error: {$exception->getMessage()} ({$exception->getCode()})");

            $request->markUnknown(); // Do not throw error
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetStatus &&
            $request->getFirstModel() instanceof PaymentInterface
            ;
    }
}
