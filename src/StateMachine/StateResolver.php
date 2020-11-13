<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\StateMachine;

use Doctrine\Common\Collections\Collection;
use Hraph\SyliusPaygreenPlugin\Types\PaymentDetailsKeys;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Refund;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\StateResolver\StateResolverInterface;
use Webmozart\Assert\Assert;

final class StateResolver implements StateResolverInterface
{
    /** @var RegistryInterface */
    private $payum;

    /**
     * StateResolver constructor.
     * @param RegistryInterface $payum
     */
    public function __construct(RegistryInterface $payum)
    {
        $this->payum = $payum;
    }

    /**
     * @param OrderInterface|BaseOrderInterface $order
     */
    public function resolve(BaseOrderInterface $order): void
    {
        Assert::isInstanceOf($order, OrderInterface::class);

        $targetTransition = $this->getTargetTransition($order);
        $lastPayment = $order->getLastPayment();

        // Do several checks
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

        $model = new ArrayObject($details);

        [$totalPayed] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_COMPLETED);
        switch ($targetTransition) {
            case OrderPaymentTransitions::TRANSITION_PARTIALLY_PAY:
                $model['amount'] -= $totalPayed;
                if ($model['amount'] <= 0) {
                    return;
                }
            // no break: execute PAY as well
            case OrderPaymentTransitions::TRANSITION_PAY:
                $gateway->execute(new Capture($model));

                break;
            case OrderPaymentTransitions::TRANSITION_PARTIALLY_REFUND:
                [$totalRefunded] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_REFUNDED);
                $model['amount'] = $totalPayed - $totalRefunded;
                if ($model['amount'] <= 0) {
                    return;
                }
            // no break execute REFUND as well
            case OrderPaymentTransitions::TRANSITION_REFUND:
                $gateway->execute(new Refund($model));

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
        // Check if need to refund
        [$refundedPaymentTotal, $refundedPayments] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_REFUNDED);

        if (0 < $refundedPayments->count() && $refundedPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_REFUND;
        }

        if (0 < $refundedPaymentTotal && $refundedPaymentTotal < $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_REFUND;
        }

        // Check if need to pay
        [$completedPaymentTotal, $completedPayments] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_COMPLETED);

        if (
            (0 < $completedPayments->count() && $completedPaymentTotal >= $order->getTotal()) ||
            $order->getPayments()->isEmpty()
        ) {
            return OrderPaymentTransitions::TRANSITION_PAY;
        }

        if (0 < $completedPaymentTotal && $completedPaymentTotal < $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_PAY;
        }

        // Check if need to authorize
        [$authorizedPaymentTotal, $authorizedPayments] = $this->getPaymentTotalWithState($order, PaymentInterface::STATE_AUTHORIZED);

        if (0 < $authorizedPayments->count() && $authorizedPaymentTotal >= $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_AUTHORIZE;
        }

        if (0 < $authorizedPaymentTotal && $authorizedPaymentTotal < $order->getTotal()) {
            return OrderPaymentTransitions::TRANSITION_PARTIALLY_AUTHORIZE;
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
