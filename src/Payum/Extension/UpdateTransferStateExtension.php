<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Extension;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Request\GetTransferStatus;
use Hraph\SyliusPaygreenPlugin\Types\TransferTransitions;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;
use SM\Factory\FactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\StateMachine\StateMachineInterface;
use Webmozart\Assert\Assert;

class UpdateTransferStateExtension implements ExtensionInterface
{
    /** @var FactoryInterface */
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
        $previousStack = $context->getPrevious();
        $previousStackSize = count($previousStack);

        if ($previousStackSize > 1) {
            return;
        }

        if ($previousStackSize === 1) {
            $previousActionClassName = get_class($previousStack[0]->getAction());
            if (false === stripos($previousActionClassName, 'NotifyNullAction')) {
                return;
            }
        }

        $request = $context->getRequest();

        if (!$request instanceof Generic) {
            return;
        }

        if (!$request instanceof GetStatusInterface && !$request instanceof Notify) {
            return;
        }

        $transfer = $request->getFirstModel();

        if (!$transfer instanceof PaygreenTransferInterface) {
            return;
        }

        if (null !== $context->getException()) {
            return;
        }

        $context->getGateway()->execute($status = new GetTransferStatus($transfer));
        $value = $status->getValue();
        if ($transfer->getState() !== $value && PaymentInterface::STATE_UNKNOWN !== $value) {
            $this->updateTransferState($transfer, $value);
        }
    }

    private function updateTransferState(PaygreenTransferInterface $transfer, string $nextState): void
    {
        $stateMachine = $this->factory->get($transfer, TransferTransitions::GRAPH);

        /** @var StateMachineInterface $stateMachine */
        Assert::isInstanceOf($stateMachine, StateMachineInterface::class);

        if (null !== $transition = $stateMachine->getTransitionToState($nextState)) {
            $stateMachine->apply($transition);
        }
    }
}
