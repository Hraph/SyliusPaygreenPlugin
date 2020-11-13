<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactory;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactoryMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use RuntimeException;

final class CaptureAction extends BaseApiAwareAction implements CaptureActionInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * @inheritDoc
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $this->tokenFactory) {
            throw new RuntimeException();
        }

        /** @var TokenInterface $token */
        $token = $request->getToken(); // Get current token

        // Create a notify token to get status updates from PayGreen
        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());
//        $refundToken = $this->tokenFactory->createRefundToken($token->getGatewayName(), $token->getDetails());

        $details['notified_url'] = $notifyToken->getTargetUrl();
        $details['returned_url'] = $token->getAfterUrl();

        $metadata = $details['metadata'];
//        $metadata['refund_token'] = $refundToken->getHash();
        $details['metadata'] = $metadata;

        if (true === $this->api->isMultipleTimePayment()) {
            $details[PaymentDetailsKeys::FACTORY_USED] = PaygreenGatewayFactoryMultiple::FACTORY_NAME; // Save factory used
            $this->gateway->execute(new CreatePaymentMultiple($details));
        }

        elseif (false === $this->api->isMultipleTimePayment()) {
            $details[PaymentDetailsKeys::FACTORY_USED] = PaygreenGatewayFactory::FACTORY_NAME; // Save factory used
            $this->gateway->execute(new CreatePayment($details));
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
