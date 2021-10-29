<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\PaygreenApi\ApiException;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactory;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Api\CreateTransfer;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Transfer;
use Hraph\SyliusPaygreenPlugin\Provider\GatewayConfigProvider;
use Hraph\SyliusPaygreenPlugin\Types\TransferDetailsKeys;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * TransferAction is called by Payum controller
 * Class TransferAction
 * @package Hraph\SyliusPaygreenPlugin\Payum\Action
 */
final class TransferAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GenericTokenFactoryAwareTrait;
    use GatewayAwareTrait;

    private LoggerInterface $logger;
    private GatewayConfigProvider $gatewayConfigProvider;

    public function __construct(LoggerInterface $logger, GatewayConfigProvider $gatewayConfigProvider)
    {
        $this->logger = $logger;
        $this->gatewayConfigProvider = $gatewayConfigProvider;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @param Transfer $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (null === $this->tokenFactory) {
            throw new RuntimeException();
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());

        /** @var PaygreenTransferInterface $transfer */
        $transfer = $request->getFirstModel();

        /** @var TokenInterface|null $token */
        $token = $request->getToken(); // Get current token if present. If coming from a payum controller, token should be present
        $gatewayConfig = $this->gatewayConfigProvider->provideByFactoryName(PaygreenGatewayFactory::FACTORY_NAME); // To create a notify token we need a valid gateway config

        // Create a notify token to get status updates from PayGreen
        $notifyToken = $this->tokenFactory->createNotifyToken((null !== $token) ? $token->getGatewayName() : $gatewayConfig->getGatewayName(), (null !== $token) ? $token->getDetails() : $transfer);
        $details[TransferDetailsKeys::NOTIFIED_URL] = $notifyToken->getTargetUrl();

        try {
            $this->gateway->execute(new CreateTransfer($details));
        }
        catch (ApiException $exception){
            $this->logger->error("PayGreen Transfer error: {$exception->getMessage()} ({$exception->getCode()})");
            throw new PaygreenException("PayGreen Transfer error: {$exception->getMessage()} ({$exception->getCode()})" . ((null !== $transfer->getInternalId()) ? " - TransactionId={$transfer->getInternalId()}" : ""), PaygreenException::CODE_PAYUM);
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
