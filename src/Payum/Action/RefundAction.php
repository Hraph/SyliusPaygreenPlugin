<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Helper\ConvertRefundDataInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class RefundAction extends BaseApiGatewayAwareAction implements RefundActionInterface
{
    /**
     * @var ConvertRefundDataInterface
     */
    private ConvertRefundDataInterface $convertOrderRefundData;


    /**
     * RefundAction constructor.
     * @param ConvertRefundDataInterface $convertOrderRefundData
     * @param LoggerInterface $logger
     */
    public function __construct(ConvertRefundDataInterface $convertOrderRefundData, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->convertOrderRefundData = $convertOrderRefundData;
    }

    /**
     * {@inheritdoc}
     * @throws PaygreenException
     */
    public function execute($request): void
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
        catch (ApiException $exception){
            $this->logger->error("PayGreen Refund error: {$exception->getMessage()} ({$exception->getCode()})");

            throw new PaygreenException("PayGreen Refund error: {$exception->getMessage()} ({$exception->getCode()})", PaygreenException::CODE_PAYUM); // Will be catch by state machine
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
