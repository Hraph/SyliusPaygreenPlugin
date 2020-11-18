<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\PaygreenApi\Model\PayinsCard;
use Hraph\PaygreenApi\Model\PayinsRecc;
use Hraph\PaygreenApi\Model\PayinsReccOrderDetails;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Reply\HttpPostRedirect;

class CreatePaymentMultipleAction extends BaseRenderableAction implements BaseRenderableActionInterface
{
    /**
     * @param mixed $request
     * @throws ApiException
     */
    public function execute($request)
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());
        $doRedirectOrRender = true; // Redirect or render after transaction

        // Create payins object for PayGreen API from ConvertAction
        $payinsRecc = new PayinsRecc($details->toUnsafeArrayWithoutLocal());
        $payinsRecc
            ->setBuyer(new PayinsBuyer($details['buyer']))
            ->setOrderDetails(new PayinsReccOrderDetails($details['order_details']));

        // Fingerprint confirmation
        if (isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID])) {
            $payinsCard = new PayinsCard();
            $payinsCard->setToken($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID]);
            $payinsRecc->setCard($payinsCard);
            $doRedirectOrRender = false; // Do nothing
        }

        try {
            $paymentRequest = $this
                ->api
                ->getPayinsTransactionApi()
                ->apiIdentifiantPayinsTransactionXtimePost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $payinsRecc);

            if (!is_null($paymentRequest->getData()) && !is_null($paymentRequest->getData()->getId())) {
                // Save transaction id for status action
                $details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID] = $paymentRequest->getData()->getId();
            }
            else
                throw new ApiException("Invalid API transaction data.");
        }
        catch (ApiException $e){
            throw new RuntimeException("PayGreen API Error: {$e->getMessage()}", $e->getCode());
        }

        if (!$doRedirectOrRender) // Background request: no need to redirect
            return;

        // API has returned a redirect url
        if (!is_null($paymentRequest->getData()->getUrl()))
            $this->redirectOrRenderUrl($paymentRequest->getData()->getUrl());

        // Otherwise use returnedUrl
        else
            throw new HttpPostRedirect($details[PaymentDetailsKeys::RETURNED_URL]);
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof CreatePaymentMultiple &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
