<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\Request\GetTransferStatus;
use Hraph\SyliusPaygreenPlugin\Types\ApiTransferStatus;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Psr\Log\LoggerInterface;

/**
 * Class TransferStatusAction is called by Payum controller
 * Check the status of the transfer after create
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class TransferStatusAction extends BaseApiGatewayAwareAction implements TransferStatusActionInterface
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
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->gateway->execute($this->getHttpRequest); // Get POST/GET data and query from request

        /** @var PaygreenTransferInterface|null $transfer */
        $transfer = $request->getModel();

        if (null === $transfer || null === $transfer->getInternalId()) { // Invalid transfer
            $request->markFailed();
            return;
        }

        try {
            // Search transaction
            $transferData = $this
                ->api
                ->getPayoutTransferApi()
                ->apiIdentifiantPayoutTransferIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $transfer->getInternalId());

            // Got transaction and valid status
            if (!is_null($transferData->getData()) && !is_null($transferData->getData()->getResult()) && !is_null($transferData->getData()->getResult()->getStatus())) {

                switch ($transferData->getData()->getResult()->getStatus()){
                    case ApiTransferStatus::STATUS_CANCELLED:
                        $request->markCanceled();
                        break;

                    case ApiTransferStatus::STATUS_SUCCEEDED:
                        $request->markSucceeded();

                    case ApiTransferStatus::STATUS_PENDING:
                        $request->markPending();
                        break;

                    case ApiTransferStatus::STATUS_FAILED:
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
            $request instanceof GetTransferStatus &&
            $request->getFirstModel() instanceof PaygreenTransferInterface
            ;
    }
}
