<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\PaygreenApi\Model\PayinsRecc;
use Hraph\PaygreenApi\Model\PayinsReccOrderDetails;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
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

        // Create payins object for PayGreen API from ConvertAction
        $payinsRecc = new PayinsRecc($details->toUnsafeArrayWithoutLocal());
        $payinsRecc
            ->setBuyer(new PayinsBuyer($details['buyer']))
            ->setOrderDetails(new PayinsReccOrderDetails($details['order_details']));

        if (isset($details[PaymentDetailsKeys::PAYGREEN_FINGERPRINT_ID]))
            $payinsRecc->setIdFingerprint($details[PaymentDetailsKeys::PAYGREEN_FINGERPRINT_ID]);

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
                throw new ApiException("Invalid API data exception. Wrong id!");

        }
        catch (\Exception $e){
            throw new ApiException(sprintf('Error with create payment multiple with: %s', $e->getMessage()));
        }

        // API has returned a redirect url
        if (!is_null($paymentRequest->getData()->getUrl()))
            $this->renderUrl($paymentRequest->getData()->getUrl());

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
