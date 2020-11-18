<?php


namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactory;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactoryMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateFingerprint;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use RuntimeException;

class AuthorizeAction extends BaseApiAwareAction implements AuthorizeActionInterface
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

        // Create token only if never did
        if (false === isset($details[PaymentDetailsKeys::NOTIFIED_URL])) {
            // Create a notify token to get status updates from PayGreen
            $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());

            $details[PaymentDetailsKeys::NOTIFIED_URL] = $notifyToken->getTargetUrl();
        }
        if (false === isset($details[PaymentDetailsKeys::RETURNED_URL])) {
            $details[PaymentDetailsKeys::RETURNED_URL] = $token->getAfterUrl();
        }

        // Save factory used
        if ($this->api->isMultipleTimePayment()) { // Multiple time payment
            $details[PaymentDetailsKeys::FACTORY_USED] = PaygreenGatewayFactoryMultiple::FACTORY_NAME;
        }
        else { // Direct payment
            $details[PaymentDetailsKeys::FACTORY_USED] = PaygreenGatewayFactory::FACTORY_NAME;
        }

        $this->gateway->execute(new CreateFingerprint($details));
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
