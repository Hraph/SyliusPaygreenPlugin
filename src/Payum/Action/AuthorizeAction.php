<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactory;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactoryMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateFingerprint;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use RuntimeException;

/**
 * AuthorizeAction is called by Payum controller
 * Class AuthorizeAction
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class AuthorizeAction extends BaseApiGatewayAwareAction implements ActionInterface, GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @inheritDoc
     *
     * @param Authorize $request
     */
    public function execute($request): void
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

        try {
            $this->gateway->execute(new CreateFingerprint($details));
        }
        catch (ApiException $exception){
            $this->logger->error("PayGreen Authorize error: {$exception->getMessage()} ({$exception->getCode()})");
            return; // Cause Payum controller is not catching exception we cannot throw one. Will be redirected to return url and state will be determined by statusAction
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
