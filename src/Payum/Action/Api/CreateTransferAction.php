<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action\Api;


use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Transfer;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateTransfer;
use Hraph\SyliusPaygreenPlugin\Types\TransferDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;

class CreateTransferAction extends BaseApiGatewayAwareAction
{
    /**
     * @throws ApiException
     */
    public function execute($request)
    {
        $details = ArrayObject::ensureArrayObject($request->getModel());

        // Create transfer object for PayGreen API from ConvertAction
        $transfer = new Transfer($details->toUnsafeArrayWithoutLocal());

        $paymentRequest = $this
            ->api
            ->getPayoutTransferApi()
            ->apiIdentifiantPayoutTransferPost($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $transfer);

        if (!is_null($paymentRequest->getData()) && !is_null($paymentRequest->getData()->getId())) {
            // Save transaction id for status action
            $details[TransferDetailsKeys::PAYGREEN_TRANSFER_ID] = $paymentRequest->getData()->getId();
        }
        else
            throw new ApiException("Invalid API transfer data.");
    }

    public function supports($request)
    {
        return
            $request instanceof CreateTransfer &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
