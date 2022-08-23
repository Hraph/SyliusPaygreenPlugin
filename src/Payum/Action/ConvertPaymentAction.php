<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\SyliusPaygreenPlugin\Helper\PaymentDescriptionInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Convert;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class ConvertPaymentAction extends BaseApiGatewayAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    private PaymentDescriptionInterface $paymentDescription;
    private ?string $orderIdPrefix;

    /**
     * ConvertPaymentAction constructor.
     * @param PaymentDescriptionInterface $paymentDescription
     * @param string|null $orderIdPrefix
     * @param LoggerInterface $logger
     */
    public function __construct(PaymentDescriptionInterface $paymentDescription, ?string $orderIdPrefix,  LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->paymentDescription = $paymentDescription;
        $this->orderIdPrefix = $orderIdPrefix;
    }


    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        $orderIdPrefix = (null !== $this->orderIdPrefix) ? $this->orderIdPrefix . '-' : '';

        $details = [
            'amount' => $payment->getAmount(),
            'currency' => $payment->getCurrencyCode(),
            'buyer' => [
                'id' => $order->getCustomer()->getId() ?? 0,
                'email' => $order->getCustomer()->getEmail(),
                'country' => $order->getBillingAddress()->getCountryCode(),
                'first_name' => $order->getBillingAddress()->getFirstName(),
                'last_name' => $order->getBillingAddress()->getLastName(),
            ],
            'billing_address' => [
                'first_name' => $order->getBillingAddress()->getFirstName(),
                'last_name' => $order->getBillingAddress()->getLastName(),
                'address' => $order->getBillingAddress()->getStreet(),
                'zip_code' => $order->getBillingAddress()->getPostcode(),
                'city' => $order->getBillingAddress()->getCity(),
                'country' => $order->getBillingAddress()->getCountryCode(),
            ],
            "order_id" => "{$orderIdPrefix}{$order->getNumber()}-{$order->getPayments()->count()}", // Cause an order ID is unique for PayGreen we need to add paymentId in case of new attempt
            "payment_type" => $this->api->getPaymentType(),
            'description' => $this->paymentDescription->getPaymentDescription($payment, $order),
            'metadata' => [
                'payment_id' => $payment->getId(),
                'order_id' => $order->getId(),
            ],
            'ttl' => 'PT20M'
        ];

        if (true === $this->api->isMultipleTimePayment()) {
            $details['order_details'] = [
                'count' => $this->api->getMultipleTimePaymentTimes(),
                'cycle' => 40, // Cycle 40 is monthly
                'day' => -1, // Same day as today
            ];
        }

        // Set payment to change API config depending on context
        $this->apiFactory->setPaymentContextForConfigResolver($payment);

        $request->setResult($details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
            ;
    }
}
