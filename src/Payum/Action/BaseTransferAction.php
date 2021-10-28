<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Request\GetTransferStatus;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Transfer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;


final class BaseTransferAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @inheritDoc
     *
     * @param Transfer $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $transfer PaygreenTransferInterface */
        $transfer = $request->getModel();

        $this->gateway->execute($status = new GetTransferStatus($transfer));
        if ($status->isNew()) {
            $this->gateway->execute($convert = new Convert($transfer, 'array', $request->getToken()));

            $transfer->setDetails($convert->getResult());
        }

        $details = ArrayObject::ensureArrayObject($transfer->getDetails());

        $request->setModel($details);
        try {
            $this->gateway->execute($request);
        } finally {
            $transfer->setDetails((array) $details);
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return
            $request instanceof Transfer &&
            $request->getModel() instanceof PaygreenTransferInterface
            ;
    }
}
