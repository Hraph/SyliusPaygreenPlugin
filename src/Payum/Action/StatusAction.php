<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Client\Adapter\PaygreenPaymentApiStatusAdapter;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * Class StatusAction is called by Payum controller
 * Check the status of the payment after capture and notify
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class StatusAction extends BaseApiGatewayAwareAction implements ActionInterface
{
    private GetHttpRequest $getHttpRequest;
    private PaygreenPaymentApiStatusAdapter $apiStatusAdapter;

    public function __construct(GetHttpRequest $getHttpRequest, PaygreenPaymentApiStatusAdapter $apiStatusAdapter, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->getHttpRequest = $getHttpRequest;
        $this->apiStatusAdapter = $apiStatusAdapter;
    }

    /**
     * @inheritDoc
     * @param GetStatus $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->gateway->execute($this->getHttpRequest); // Get POST/GET data and query from request

        /** @var PaymentInterface $paymentModel */
        $paymentModel = $request->getModel();
        $paymentDetails = $paymentModel->getDetails();
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
            $payment = $this
                ->api
                ->getPayinsTransactionApi()
                ->apiIdentifiantPayinsTransactionIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $pid);
            $paymentData = $payment->getData();

            // Got transaction and valid status
            if (!is_null($paymentData) && !is_null($paymentData->getResult()) && !is_null($paymentData->getResult()->getStatus())) {
                $state = $this->apiStatusAdapter->adapt($paymentData->getResult()->getStatus(),$isFingerprintTransaction);

                switch ($state){
                    case PaymentInterface::STATE_CANCELLED:
                        $request->markCanceled();
                        break;
                    case PaymentInterface::STATE_COMPLETED:
                        $request->markCaptured(); // Succeeded when payment
                        break;
                    case PaymentInterface::STATE_AUTHORIZED:
                        $request->markAuthorized(); // Authorized when Fingerprint
                        break;
                    case PaymentInterface::STATE_NEW:
                        $request->markNew();
                        break;
                    case PaymentInterface::STATE_REFUNDED:
                        $request->markRefunded();
                        break;
                    case PaymentInterface::STATE_FAILED:
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
            $this->logger->error("PayGreen Status error: {$exception->getMessage()} ({$exception->getCode()}) - TransactionId=$pid");

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
