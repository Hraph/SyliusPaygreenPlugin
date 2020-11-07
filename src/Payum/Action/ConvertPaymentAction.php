<?php


namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiAwareAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class ConvertPaymentAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;


    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $customer = $order->getCustomer();

        $details = [
            'amount' => $payment->getAmount(),
            'currencyCode' => $payment->getCurrencyCode(),
            'customer' => [
                'firstName' => $order->getCustomer()->getFirstName(),
                'lastName' => $order->getCustomer()->getLastName(),
                'fullName' => $order->getCustomer()->getFullName(),
                'email' => $order->getCustomer()->getEmail(),
            ],
//            'description' => $this->paymentDescription->getPaymentDescription($payment, $method, $order),
            'metadata' => [
                'order_id' => $order->getId(),
                'customer_id' => $customer->getId() ?? null
            ],
        ];

//        if (true === $this->api->isRecurringSubscription()) {
//            $config = $this->api->getConfig();
//
//            $details['times'] = $config['times'];
//            $details['interval'] = $config['interval'];
//        }

        $request->setResult($details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
            ;
    }
}
