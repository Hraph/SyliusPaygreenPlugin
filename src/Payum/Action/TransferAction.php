<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactory;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactoryMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePayment;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreatePaymentMultiple;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateTransfer;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Transfer;
use Hraph\SyliusPaygreenPlugin\Types\TransferDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use RuntimeException;

/**
 * TransferAction is called by Payum controller
 * Class TransferAction
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class TransferAction extends BaseApiGatewayAwareAction implements TransferActionInterface
{
    use GenericTokenFactoryAwareTrait;

    /**
     * @inheritDoc
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

        // Create a notify token to get status updates from PayGreen
        $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $details[TransferDetailsKeys::NOTIFIED_URL] = $notifyToken->getTargetUrl();

        try {
            $this->gateway->execute(new CreateTransfer($details));
        }
        catch (ApiException $exception){
            $this->logger->error("PayGreen Transfer error: {$exception->getMessage()} ({$exception->getCode()})");
            return; // Cause Payum controller is not catching exception we cannot throw one. Will be redirected to return url and state will be determined by statusAction
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof Transfer &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
