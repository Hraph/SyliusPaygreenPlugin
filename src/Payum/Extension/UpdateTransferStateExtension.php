<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Extension;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Request\GetTransferStatus;
use Hraph\SyliusPaygreenPlugin\StateMachine\PaygreenTransferStateApplicator;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;

class UpdateTransferStateExtension implements ExtensionInterface
{
    private PaygreenTransferStateApplicator $stateApplicator;

    public function __construct(PaygreenTransferStateApplicator $stateApplicator)
    {
        $this->stateApplicator = $stateApplicator;
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

        $this->stateApplicator->apply($transfer, $value); // Applicator will check if state is not UNKNOWN
    }
}
