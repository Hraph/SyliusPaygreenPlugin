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

        
    }

    /**
     * @inheritDoc
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof PaymentInterface
            ;
    }
}
