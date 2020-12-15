<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\CaptureAuthorized;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

/**
 * CaptureAuthorized is called by the admin to process the payment
 * Class CaptureAuthorizedAction
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
class CaptureAuthorizedAction extends BaseApiGatewayAwareAction implements CaptureAuthorizedActionInterface
{
    /**
     * @inheritDoc
     * @throws PaygreenException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false === isset($details[PaymentDetailsKeys::RETURNED_URL]) ||
            false === isset($details[PaymentDetailsKeys::NOTIFIED_URL]) ||
            false === isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID]) ||
            false === isset($details[PaymentDetailsKeys::FACTORY_USED]))
            return; // Cardprint id is mandatory. Also return and notify url should already been set when authorize

        try {
            if ($this->api->isMultipleTimePayment()) // Multiple time payment
                $this->gateway->execute(new CreatePaymentMultiple($details));

            else // Direct payment
                $this->gateway->execute(new CreatePayment($details));
        }
        catch (ApiException $exception){
            $this->logger->error("PayGreen Capture authorized error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Capture authorized error: {$exception->getMessage()} ({$exception->getCode()})", PaygreenException::CODE_PAYUM); // Will be catch by state machine
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof CaptureAuthorized &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }


}
