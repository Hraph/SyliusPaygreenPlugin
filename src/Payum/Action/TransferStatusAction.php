<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Client\Adapter\PaygreenPaymentApiStatusAdapter;
use Hraph\SyliusPaygreenPlugin\Client\Adapter\PaygreenTransferApiStatusAdapter;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\Request\GetTransferStatus;
use Hraph\SyliusPaygreenPlugin\Types\TransferDetailsKeys;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Psr\Log\LoggerInterface;

/**
 * Class TransferStatusAction is called by Payum controller
 * Check the status of the transfer after create
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class TransferStatusAction extends BaseApiGatewayAwareAction implements ActionInterface
{
    private GetHttpRequest $getHttpRequest;
    private PaygreenTransferApiStatusAdapter $apiStatusAdapter;

    public function __construct(GetHttpRequest $getHttpRequest, PaygreenTransferApiStatusAdapter $apiStatusAdapter, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->getHttpRequest = $getHttpRequest;
        $this->apiStatusAdapter = $apiStatusAdapter;
    }

    /**
     * @inheritDoc
     * @param GetTransferStatus $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->gateway->execute($this->getHttpRequest); // Get POST/GET data and query from request

        /** @var PaygreenTransferInterface $transferModel */
        $transferModel = $request->getModel();
        $transferDetails = $transferModel->getDetails();
        $tid = null;

        // Transfer already executed
        if (true === isset($transferDetails[TransferDetailsKeys::PAYGREEN_TRANSFER_ID])) {
            $tid = $transferDetails[TransferDetailsKeys::PAYGREEN_TRANSFER_ID];
        }
        else { // Transaction ID is not set in transfer data. Invalid transfer
            $request->markNew();
            return;
        }

        try {
            // Search transaction
            $transfer = $this
                ->api
                ->getPayoutTransferApi()
                ->apiIdentifiantPayoutTransferIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $tid);
            $transferData = $transfer->getData();

            // Got transaction and valid status
            if (!is_null($transferData) && !is_null($transferData->getResult()) && !is_null($transferData->getResult()->getStatus())) {
                $state = $this->apiStatusAdapter->adapt($transferData->getResult()->getStatus());

                switch ($state){
                    case PaygreenTransferInterface::STATE_CANCELLED:
                        $request->markCanceled();
                        break;
                    case PaygreenTransferInterface::STATE_COMPLETED:
                        $request->markCompleted();
                        break;
                    case PaygreenTransferInterface::STATE_PROCESSING:
                        $request->markProcessing();
                        break;
                    case PaygreenTransferInterface::STATE_FAILED:
                        $request->markFailed();
                        break;
                    default:
                        $request->markUnknown();
                        break;
                }
            }
            else throw new ApiException("Invalid API transfer data.");
        }
        catch (ApiException $exception){
            $this->logger->error("PayGreen Status error: {$exception->getMessage()} ({$exception->getCode()}) - TransactionId=$tid");

            $request->markUnknown(); // Do not throw error
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetTransferStatus &&
            $request->getFirstModel() instanceof PaygreenTransferInterface
            ;
    }
}
