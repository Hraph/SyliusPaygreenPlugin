<?php

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;

final class StatusAction implements ActionInterface
{
    public const STATUS_CAPTURED = 'CAPTURED';

    public const STATUS_CREATED = 'CREATED';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_PROCESSING = 'PROCESSING';

    /**
     * @inheritDoc
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var array $model */
        $model = $request->getModel();

        if ($model['status'] === self::STATUS_CREATED) {
            $request->markNew();

            return;
        }

        if ($model['status'] === self::STATUS_CAPTURED) {
            $request->markPending();

            return;
        }

        if ($model['status'] === self::STATUS_COMPLETED) {
            $request->markCaptured();

            return;
        }

        $request->markFailed();
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatus &&
            $request->getFirstModel() instanceof PaymentInterface
            ;
    }
}
