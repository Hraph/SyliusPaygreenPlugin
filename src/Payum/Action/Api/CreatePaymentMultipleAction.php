<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Payins;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\PaygreenApi\Model\PayinsRecc;
use Hraph\PaygreenApi\Model\PayinsReccOrderDetails;
use Hraph\SyliusPaygreenPlugin\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;

class CreatePaymentMultipleAction extends BaseApiAwareAction implements ActionInterface
{

    /**
     * @param mixed $request
     * @throws ApiException
     */
    public function execute($request)
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $payinsRecc = new PayinsRecc();
        $buyer = new PayinsBuyer();
        $orderDetails = new PayinsReccOrderDetails();

        $buyer
            ->setId($details['metadata']['customer_id'])
            ->setEmail($details['customer']['email'])
            ->setFirstName($details['customer']['firstName'])
            ->setLastName($details['customer']['lastName']);

        $orderDetails
            ->setCount($details['times'])
            ->setCycle(40) // Cycle 40 is monthly
            ->setDay(-1); // Same day as today

        // Create payins object for PayGreen API
        $payinsRecc
            ->setAmount($details['amount'])
            ->setOrderDetails($orderDetails)
            ->setBuyer($buyer)
            ->setOrderId("{$details['metadata']['order_id']}-{$details['metadata']['payment_id']}") // Cause an order ID is unique for PayGreen we need to add paymentId in case of new attempt
            ->setPaymentType($this->api->getPaymentType())
            ->setCurrency($details['currencyCode'])
            ->setNotifiedUrl($details['notifiedUrl'])
            ->setReturnedUrl($details['returnedUrl'])
            ->setMetadata($details['metadata']);


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
            $request instanceof CreatePaymentMultiple &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
