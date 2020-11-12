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
        $this->gateway->execute($this->getHttpRequest); // Get POST data and query from request

        // Transaction check. And transaction ID must be set in payment details
        if ((true === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]) ||
                true === isset($details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID]) ||
                true === isset($details[PaymentDetailsKeys::PAYGREEN_FINGERPRINT_ID]))
            && true === isset($this->getHttpRequest->request['pid'])) {
            try {
                $payment = $this
                    ->api
                    ->getPayinsTransactionApi()
                    ->apiIdentifiantPayinsTransactionIdGet($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $this->getHttpRequest->request['pid']);

                // Todo check order id and update transaction id
            }
            catch (\Exception $e){
                throw new ApiException(sprintf("Error with get transaction from PayGreen with %s", $e->getMessage()));
            }

            throw new HttpResponse('OK', Response::HTTP_OK);
        }

        throw new HttpResponse('Invalid data', Response::HTTP_BAD_REQUEST); // Invalid pid
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
