<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Payins;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\SyliusPaygreenPlugin\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;

class CreatePaymentAction extends BaseApiAwareAction implements ActionInterface
{

    /**
     * @param mixed $request
     * @throws ApiException
     */
    public function execute($request)
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $payins = new Payins();
        $buyer = new PayinsBuyer();

        // Create payins object for PayGreen API
        $buyer->setId($details['metadata']['customer_id'])
            ->setEmail($details['customer']['email'])
            ->setFirstName($details['customer']['firstName'])
            ->setLastName($details['customer']['lastName']);

        $payins->setAmount($details['amount'])
            ->setOrderId("{$details['metadata']['order_id']}-{$details['metadata']['payment_id']}") // Cause an order ID is unique for PayGreen we need to add paymentId in case of new attempt
            ->setBuyer($buyer)
            ->setPaymentType($this->api->getPaymentType())
            ->setCurrency($details['currencyCode'])
            ->setNotifiedUrl($details['notifiedUrl'])
            ->setReturnedUrl($details['returnedUrl']);


        try {
            $paymentRequest = $this
                ->api
                ->getPayinsTransactionApi()
                ->apiIdentifiantPayinsTransactionCashPost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $payins);

            if (!is_null($paymentRequest->getData()) && !is_null($paymentRequest->getData()->getId())) {
                // Save transaction id for status action
                $details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID] = $paymentRequest->getData()->getId();
            }
            else
                throw new ApiException("Invalid API data exception. Wrong id!");

        }
        catch (ApiException $e) {
            throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
        }
        catch (\Exception $e){
            throw new ApiException(sprintf('Error with create payment with: %s', $e->getMessage()));
        }

        // API has returned a redirect url
        if (!is_null($paymentRequest->getData()->getUrl()))
            throw new HttpPostRedirect($paymentRequest->getData()->getUrl());

        // Otherwise use returnedUrl
        else
            throw new HttpPostRedirect($details['returnedUrl']);
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof CreatePayment &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
