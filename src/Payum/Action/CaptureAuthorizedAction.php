<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\CaptureAuthorized;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;

class CaptureAuthorizedAction extends BaseApiAwareAction implements CaptureAuthorizedActionInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false === isset($details[PaymentDetailsKeys::RETURNED_URL]) ||
            false === isset($details[PaymentDetailsKeys::NOTIFIED_URL]) ||
            false === isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID]) ||
            false === isset($details[PaymentDetailsKeys::FACTORY_USED]))
            return; // Cardprint id is mandatory. Also return and notify url should already been set when authorize

        if ($this->api->isMultipleTimePayment()) // Multiple time payment
            $this->gateway->execute(new CreatePaymentMultiple($details));

        else // Direct payment
            $this->gateway->execute(new CreatePayment($details));
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureAuthorized &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }


}
