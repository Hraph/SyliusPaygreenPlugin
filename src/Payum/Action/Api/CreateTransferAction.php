<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Payins;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\PaygreenApi\Model\PayinsCard;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateTransfer;
use Hraph\SyliusPaygreenPlugin\Types\TransferDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;

class CreateTransferAction extends BaseApiGatewayAwareAction
{
    public function execute($request)
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());
        $doRedirectOrRender = true; // Redirect or render after transaction
        // Create payins object for PayGreen API from ConvertAction
        $payins = new Payins($details->toUnsafeArrayWithoutLocal());
        $payins->setBuyer(new PayinsBuyer($details['buyer']));

        // Fingerprint confirmation
        if (isset($details[TransferDetailsKeys::PAYGREEN_CARDPRINT_ID])) {
            $payinsCard = new PayinsCard();
            $payinsCard->setToken($details[TransferDetailsKeys::PAYGREEN_CARDPRINT_ID]);
            $payins->setCard($payinsCard);
            $doRedirectOrRender = false; // Do nothing
        }

        $paymentRequest = $this
            ->api
            ->getPayinsTransactionApi()
            ->apiIdentifiantPayinsTransactionCashPost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $payins);

        if (!is_null($paymentRequest->getData()) && !is_null($paymentRequest->getData()->getId())) {
            // Save transaction id for status action
            $details[TransferDetailsKeys::PAYGREEN_TRANSACTION_ID] = $paymentRequest->getData()->getId();
        }
        else
            throw new ApiException("Invalid API transaction data.");

        if (!$doRedirectOrRender) // Background request: no need to redirect
            return;

        // API has returned a redirect url
        if (!is_null($paymentRequest->getData()->getUrl()))
            $this->redirectOrRenderUrl($paymentRequest->getData()->getUrl());

        // Otherwise use returnedUrl
        else
            throw new HttpPostRedirect($details[TransferDetailsKeys::RETURNED_URL]);
    }

    public function supports($request)
    {
        return
            $request instanceof CreateTransfer &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
