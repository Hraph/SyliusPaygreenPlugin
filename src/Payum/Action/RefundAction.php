<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Helper\ConvertRefundDataInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;

class RefundAction extends BaseApiAwareAction implements RefundActionInterface
{
    use GatewayAwareTrait;

    /**
     * @var ConvertRefundDataInterface
     */
    private ConvertRefundDataInterface $convertOrderRefundData;


    /**
     * RefundAction constructor.
     * @param ConvertRefundDataInterface $convertOrderRefundData
     */
    public function __construct(ConvertRefundDataInterface $convertOrderRefundData)
    {
        $this->convertOrderRefundData = $convertOrderRefundData;
    }

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        if (true === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID])){
            try {
                $refundData = $this->convertOrderRefundData->convert($details['metadata']['refund'], $payment->getCurrencyCode());

                $payment = $this
                    ->api
                    ->getPayinsTransactionApi()
                    ->apiIdentifiantPayinsTransactionIdDelete($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]);

                // TODO ADD AMOUNT (amount parameter is not available in api)

            }
            catch (ApiException $e){
                throw new UpdateHandlingException(sprintf('API call failed: %s', htmlspecialchars($e->getMessage())));
            }
        }
    }

    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
