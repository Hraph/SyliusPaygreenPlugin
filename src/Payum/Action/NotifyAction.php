<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
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
    /**
     * @var GetHttpRequest
     */
    private GetHttpRequest $getHttpRequest;

    /**
     * NotifyAction constructor.
     * @param GetHttpRequest $getHttpRequest
     * @param LoggerInterface $logger
     */
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

        // Model contains only details
        $details = ArrayObject::ensureArrayObject($request->getModel());
        $this->gateway->execute($this->getHttpRequest); // Get POST/GET data and query from request

        // Transaction check. And transaction ID must be set in payment details and present in request POST
        if ((true === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]) ||
                true === isset($details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID]) ||
                true === isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID]))
            && true === isset($this->getHttpRequest->request['pid'])) {
            try {
                $payment = $this
                    ->api
                    ->getPayinsTransactionApi()
                    ->apiIdentifiantPayinsTransactionIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $this->getHttpRequest->request['pid']);

                if (false === $payment->getSuccess())
                    throw new ApiException("Payment has not succeed" . (!empty($payment->getMessage()) ? ": ({$payment->getMessage()})." : "."));
            }
            catch (\Exception $exception){
                $this->logger->error("PayGreen Notify error: {$exception->getMessage()} ({$exception->getCode()})");

                throw new HttpResponse('Invalid API request', Response::HTTP_BAD_REQUEST); // Invalid request
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
