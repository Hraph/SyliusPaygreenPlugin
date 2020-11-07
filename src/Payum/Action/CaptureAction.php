<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\PaygreenApi\Model\Payins;
use Hraph\PaygreenApi\Model\PayinsBuyer;
use Hraph\SyliusPaygreenPlugin\Model\PaymentDetails;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Hraph\SyliusPaygreenPlugin\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use RuntimeException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

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

        // Transaction id is already in payment details: payment already been made
        if (true === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID])) {
            return;
        }

        if (null === $this->tokenFactory) {
            throw new RuntimeException();
        }

        /** @var TokenInterface $token */
        $token = $request->getToken(); // Get current token

        // Create a notify token to get status updates from PayGreen
        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $details['notifiedUrl'] = $notifyToken->getTargetUrl();
        $details['returnedUrl'] = $token->getAfterUrl();

        $metadata = $details['metadata'];
        $details['metadata'] = $metadata;

        $this->gateway->execute(new CreatePayment($details));
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
