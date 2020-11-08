<?php


namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\SyliusPaygreenPlugin\Helper\PaymentDescriptionInterface;
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
     * @var PaymentDescriptionInterface
     */
    private PaymentDescriptionInterface $paymentDescription;

    /**
     * ConvertPaymentAction constructor.
     * @param PaymentDescriptionInterface $paymentDescription
     */
    public function __construct(PaymentDescriptionInterface $paymentDescription)
    {
        $this->paymentDescription = $paymentDescription;
    }


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

        $details = [
            'amount' => $payment->getAmount(),
            'currencyCode' => $payment->getCurrencyCode(),
            'customer' => [
                'firstName' => $order->getBillingAddress()->getFirstName(),
                'lastName' => $order->getBillingAddress()->getLastName(),
                'email' => $order->getCustomer()->getEmail(),
            ],
            //'description' => $this->paymentDescription->getPaymentDescription($payment, $order),
            'metadata' => [
                'payment_id' => $payment->getId(),
                'order_id' => $order->getId(),
                'customer_id' => $order->getCustomer()->getId() ?? null,
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
