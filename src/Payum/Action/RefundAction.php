<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Helper\ConvertRefundDataInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
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

    /**
     * {@inheritdoc}
     * @throws UpdateHandlingException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        // Must have a valid transaction to be refund
        if (true === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]))
            $pid = $details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]; // Direct
        elseif (true === isset($details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID]))
            $pid = $details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID]; // Multiple
        else
            return;

        try {
            $refund = $this
                ->api
                ->getPayinsTransactionApi()
                ->apiIdentifiantPayinsTransactionIdDelete($this->api->getUsername(), $this->api->getApiKeyWithPrefix(), $pid);

            if (false === $refund->getSuccess())
                throw new ApiException("Refund has not succeed" . (!empty($refund->getMessage()) ? ": ({$refund->getMessage()})." : "."));
        }
        catch (ApiException $e){
            throw new RuntimeException("PayGreen API Error: {$e->getMessage()}", $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
