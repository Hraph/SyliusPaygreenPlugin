<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\StateMachine;

use Doctrine\Common\Collections\Collection;
use Hraph\SyliusPaygreenPlugin\Payum\Request\CaptureAuthorized;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\StateResolver\StateResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class AdminOrderPaymentStateResolver implements StateResolverInterface
{
    /** @var RegistryInterface */
    private $payum;

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * StateResolver constructor.
     * @param RequestStack $requestStack
     * @param RegistryInterface $payum
     */
    public function __construct(RequestStack $requestStack, RegistryInterface $payum)
    {
        $this->requestStack = $requestStack;
        $this->payum = $payum;
    }

    /**
     * @param OrderInterface|BaseOrderInterface $order
     */
    public function resolve(BaseOrderInterface $order): void
    {
        Assert::isInstanceOf($order, OrderInterface::class);

        $fromState = $order->getPaymentState();
        $targetTransition = $this->getTargetTransition($order);
        $currentRoute = $this->requestStack->getCurrentRequest()->get("_route");
        $lastPayment = $order->getLastPayment();

        // Only from Admin order route
        if ($currentRoute !== "sylius_admin_order_payment_complete" && $currentRoute !== "sylius_admin_order_payment_refund")
            return;

        if (null === $lastPayment) {
            return;
        }

        // Check if it's a Paygreen payment
        $details = $lastPayment->getDetails();
        if (false === isset($details[PaymentDetailsKeys::PAYGREEN_TRANSACTION_ID]) &&
            false === isset($details[PaymentDetailsKeys::PAYGREEN_MULTIPLE_TRANSACTION_ID]) &&
            false === isset($details[PaymentDetailsKeys::PAYGREEN_CARDPRINT_ID])) {
            return;
        }
        if (false === isset($details[PaymentDetailsKeys::FACTORY_USED]))
            return; // No factory name provided

        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $lastPayment->getMethod();
        if (null === $paymentMethod) {
            return;
        }

        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            return;
        }

        // Get gateway
        $gatewayFactory = $this->payum->getGatewayFactory($details[PaymentDetailsKeys::FACTORY_USED]);
        $gateway = $gatewayFactory->create($gatewayConfig->getConfig());

        [$totalPayed] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_COMPLETED);

        switch ($fromState) {
            // From Authorized to Pay
            case OrderPaymentStates::STATE_AUTHORIZED:
            case OrderPaymentStates::STATE_PARTIALLY_AUTHORIZED:
                if ($targetTransition === OrderPaymentTransitions::TRANSITION_PARTIALLY_PAY) {
                    $lastPayment->setAmount($lastPayment->getAmount() - $totalPayed); // Recount the amount
                    if ($lastPayment->getAmount() <= 0) {
                        return;
                    }
                }

                if ($targetTransition === OrderPaymentTransitions::TRANSITION_PAY)
                    $gateway->execute(new CaptureAuthorized($lastPayment)); // Capture from authorized
                break;

            // From Paid to Refund
            case OrderPaymentStates::STATE_PAID:
            case OrderPaymentStates::STATE_PARTIALLY_PAID:
                if ($targetTransition === OrderPaymentTransitions::TRANSITION_PARTIALLY_REFUND) {
                    [$totalRefunded] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_REFUNDED);
                    $lastPayment->setAmount($totalPayed - $totalRefunded); // Recount the amount
                    if ($lastPayment->getAmount() <= 0) {
                        return;
                    }
                }

                if ($targetTransition === OrderPaymentTransitions::TRANSITION_REFUND) {
                    $gateway->execute($result = new Refund($lastPayment));
                }
                break;
        }
    }

    /**
     * Get transition to apply from the current state of the payments
     * @param OrderInterface $order
     * @return string|null
     */
    private function getTargetTransition(OrderInterface $order): ?string
    {
        $refundedPaymentTotal = 0;
        $refundedPayments = $this->getPaymentsWithState($order, PaymentInterface::STATE_REFUNDED);

        foreach ($refundedPayments as $payment) {
            $refundedPaymentTotal += $payment->getAmount();
        }

        if (0 < $refundedPayments->count() && $refundedPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_REFUND;
        }

        if ($refundedPaymentTotal < $order->getTotal() && 0 < $refundedPaymentTotal) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_REFUND;
        }

        $completedPaymentTotal = 0;
        $completedPayments = $this->getPaymentsWithState($order, PaymentInterface::STATE_COMPLETED);

        foreach ($completedPayments as $payment) {
            $completedPaymentTotal += $payment->getAmount();
        }

        if (
            (0 < $completedPayments->count() && $completedPaymentTotal >= $order->getTotal()) ||
            $order->getPayments()->isEmpty()
        ) {
            return OrderPaymentTransitions::TRANSITION_PAY;
        }

        if ($completedPaymentTotal < $order->getTotal() && 0 < $completedPaymentTotal) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_PAY;
        }

        // Authorized payments
        $authorizedPaymentTotal = 0;
        $authorizedPayments = $this->getPaymentsWithState($order, PaymentInterface::STATE_AUTHORIZED);

        foreach ($authorizedPayments as $payment) {
            $authorizedPaymentTotal += $payment->getAmount();
        }

        if (0 < $authorizedPayments->count() && $authorizedPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_AUTHORIZE;
        }

        if ($authorizedPaymentTotal < $order->getTotal() && 0 < $authorizedPaymentTotal) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_AUTHORIZE;
        }

        // Processing payments
        $processingPaymentTotal = 0;
        $processingPayments = $this->getPaymentsWithState($order, PaymentInterface::STATE_PROCESSING);

        foreach ($processingPayments as $payment) {
            $processingPaymentTotal += $payment->getAmount();
        }

        if (0 < $processingPayments->count() && $processingPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_REQUEST_PAYMENT;
        }

        return null;
    }

    /**
     * @param OrderInterface $order
     * @param string $state
     * @return Collection|PaymentInterface[]
     */
    private function getPaymentsWithState(OrderInterface $order, string $state): Collection
    {
        return $order->getPayments()->filter(function (PaymentInterface $payment) use ($state): bool {
            return $state === $payment->getState();
        });
    }

    /**
     * @param OrderInterface $order
     * @param string $state
     * @return array
     */
    private function getPaymentTotalWithState(OrderInterface $order, string $state): array
    {
        $paymentTotal = 0;
        $payments = $this->getPaymentsWithState($order, $state);

        foreach ($payments as $payment) {
            $paymentTotal += $payment->getAmount();
        }

        return [$paymentTotal, $payments];
    }
}
