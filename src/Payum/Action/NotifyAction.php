<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Symfony\Component\HttpFoundation\Response;

final class NotifyAction extends BaseApiAwareAction implements NotifyActionInterface
{
    use GatewayAwareTrait;

    /**
     * @var GetHttpRequest
     */
    private GetHttpRequest $getHttpRequest;

    /**
     * NotifyAction constructor.
     * @param GetHttpRequest $getHttpRequest
     */
    public function __construct(GetHttpRequest $getHttpRequest)
    {
        $this->getHttpRequest = $getHttpRequest;
    }

    /**
     * {@inheritdoc}
     * @throws ApiException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

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
            catch (\Exception $e){
                // TODO LOG ERROR
                throw new HttpResponse('Invalid API request', Response::HTTP_BAD_REQUEST); // Invalid pid
            }

            throw new HttpResponse('OK', Response::HTTP_OK);
        }

        throw new HttpResponse('Invalid parameters', Response::HTTP_BAD_REQUEST); // Invalid pid
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
