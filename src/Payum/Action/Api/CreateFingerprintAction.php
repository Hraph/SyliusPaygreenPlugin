<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;

use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\CardPrint;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateFingerprint;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Reply\HttpPostRedirect;

class CreateFingerprintAction extends BaseRenderableAction implements BaseRenderableActionInterface
{
    /**
     * @inheritDoc
     * @throws ApiException
     */
    public function execute($request): void
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        // Create payins object for PayGreen API from ConvertAction
        $cardPrint = new CardPrint($details->toUnsafeArrayWithoutLocal());
        $cardPrint->setBuyer(new PayinsBuyer($details['buyer']));

        $paymentRequest = $this
            ->api
            ->getPayinsCardprintApi()
            ->apiIdentifiantPayinsCardprintPost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $cardPrint);

        if (!is_null($paymentRequest->getData()) && !is_null($paymentRequest->getData()->getId())) {
            // Save transaction id for status action
            $details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID] = $paymentRequest->getData()->getId();
        }
        else
            throw new ApiException("Invalid API transaction data.");

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
    public function supports($request): bool
    {
        return
            $request instanceof CreateFingerprint &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
