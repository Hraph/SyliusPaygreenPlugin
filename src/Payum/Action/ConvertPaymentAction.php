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
            'currency' => $payment->getCurrencyCode(),
            'buyer' => [
                'id' => $order->getCustomer()->getId() ?? null,
                'email' => $order->getCustomer()->getEmail(),
                'country' => $order->getBillingAddress()->getCountryCode(),
                'first_name' => $order->getBillingAddress()->getFirstName(),
                'last_name' => $order->getBillingAddress()->getLastName(),
            ],
            "order_id" => "{$order->getId()}-{$payment->getId()}", // Cause an order ID is unique for PayGreen we need to add paymentId in case of new attempt
            "payment_type" => $this->api->getPaymentType(),

            //'description' => $this->paymentDescription->getPaymentDescription($payment, $order),
            'metadata' => [
                'payment_id' => $payment->getId(),
                'order_id' => $order->getId(),
            ],
        ];

        if (true === $this->api->isMultipleTimePayment()) {
            $details['order_details'] = [
                'times' => $this->api->getMultipleTimePaymentTimes(),
                'cycle' => 40, // Cycle 40 is monthly
                'day' => -1, // Same day as today
            ];
        }
//        if (true === $this->api->isMultipleTimePayment()) {
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
