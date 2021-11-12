<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Payins;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\PaygreenApi\Model\PayinsCard;
use Hraph\PaygreenApi\Model\PayinsShippingAddress;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Reply\HttpPostRedirect;

class CreatePaymentAction extends BaseRenderableAction implements BaseRenderableActionInterface
{
    /**
     * @param mixed $request
     * @throws ApiException
     */
    public function execute($request): void
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());
        $doRedirectOrRender = true; // Redirect or render after transaction
        // Create payins object for PayGreen API from ConvertAction
        $payins = new Payins($details->toUnsafeArrayWithoutLocal());
        $payins->setBuyer(new PayinsBuyer($details['buyer']));
        $payins->setBillingAddress(new PayinsShippingAddress($details['billing_address']));

        // Fingerprint confirmation
        if (isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID])) {
            $payinsCard = new PayinsCard();
            $payinsCard->setToken($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID]);
            $payins->setCard($payinsCard);
            $doRedirectOrRender = false; // Do nothing
        }

        $paymentRequest = $this
            ->api
            ->getPayinsTransactionApi()
            ->apiIdentifiantPayinsTransactionCashPost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $payins);

        if (!is_null($paymentRequest->getData()) && !is_null($paymentRequest->getData()->getId())) {
            // Save transaction id for status action
            $details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID] = $paymentRequest->getData()->getId();
        }
        else
            throw new ApiException("Invalid API transaction data.");

        if (!$doRedirectOrRender) // Background request: no need to redirect
            return;

        // API has returned a redirect url
        if (!is_null($paymentRequest->getData()->getUrl()))
            $this->redirectOrRenderUrl($paymentRequest->getData()->getUrl());

        // Otherwise, use returnedUrl
        else
            throw new HttpPostRedirect($details[PaymentDetailsKeys::RETURNED_URL]);
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof CreatePayment &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
