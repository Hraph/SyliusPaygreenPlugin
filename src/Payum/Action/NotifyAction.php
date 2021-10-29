<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Hraph\SyliusPaygreenPlugin\Types\TransferDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * NotifyAction is called by Payum controller
 * Class NotifyAction
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class NotifyAction extends BaseApiGatewayAwareAction implements ActionInterface
{
    private GetHttpRequest $getHttpRequest;

    public function __construct(GetHttpRequest $getHttpRequest, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->getHttpRequest = $getHttpRequest;
    }

    /**
     * {@inheritdoc}
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $token = $request->getToken();

        if (null === $token || null === $token->getDetails()) {
            throw new HttpResponse('Invalid token', Response::HTTP_BAD_REQUEST);
        }

        // Model contains only details
        $details = ArrayObject::ensureArrayObject($request->getModel());
        $this->gateway->execute($this->getHttpRequest); // Get POST/GET data and query from request


        // PAYMENT Transaction check. And transaction ID must be set in payment details and present in request POST
        if ((true === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]) ||
                true === isset($details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID]) ||
                true === isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID]))
            && true === isset($this->getHttpRequest->request['pid'])) {
            try {
                $pid = $this->getHttpRequest->request['pid'];
                $payment = $this
                    ->api
                    ->getPayinsTransactionApi()
                    ->apiIdentifiantPayinsTransactionIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $pid);

                if (false === $payment->getSuccess())
                    throw new ApiException("Payment $pid has not succeed" . (!empty($payment->getMessage()) ? ": ({$payment->getMessage()})." : "."));
            }
            catch (\Exception $exception){
                $this->logger->error("PayGreen Notify error for payment $pid: {$exception->getMessage()} ({$exception->getCode()})");

                throw new HttpResponse('Invalid Payment API request', Response::HTTP_BAD_REQUEST); // Invalid request
            }

            throw new HttpResponse('OK', Response::HTTP_OK);
        }

        // TRANSFER Transaction check.
        if (true === isset($details[TransferDetailsKeys::PAYGREEN_TRANSFER_ID]) &&
            true === isset($this->getHttpRequest->request['pid'])) {
            $pid = $this->getHttpRequest->request['pid'];
            try {
                $transfer = $this
                    ->api
                    ->getPayoutTransferApi()
                    ->apiIdentifiantPayoutTransferIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $pid);

                if (false === $transfer->getSuccess())
                    throw new ApiException("Transfer $pid has not succeed" . (!empty($transfer->getMessage()) ? ": ({$transfer->getMessage()})." : "."));
            }
            catch (\Exception $exception){
                $this->logger->error("PayGreen Notify error for transfer $pid: {$exception->getMessage()} ({$exception->getCode()})");

                throw new HttpResponse('Invalid Transfer API request', Response::HTTP_BAD_REQUEST); // Invalid request
            }

            throw new HttpResponse('OK', Response::HTTP_OK);
        }

        throw new HttpResponse('Invalid parameters', Response::HTTP_BAD_REQUEST); // Invalid pid
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
