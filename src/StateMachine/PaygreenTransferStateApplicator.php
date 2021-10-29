<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\StateMachine;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Types\TransferTransitions;
use SM\Factory\FactoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\StateMachine\StateMachineInterface;
use Webmozart\Assert\Assert;

class PaygreenTransferStateApplicator
{
    private FactoryInterface $smFactory;

    public function __construct(FactoryInterface $smFactory)
    {
        $this->smFactory = $smFactory;
    }

    public function apply(PaygreenTransferInterface $transfer, string $nextState): void
    {
        if ($transfer->getState() !== $nextState && PaymentInterface::STATE_UNKNOWN !== $nextState) {
            $stateMachine = $this->smFactory->get($transfer, TransferTransitions::GRAPH);

            /** @var StateMachineInterface $stateMachine */
            Assert::isInstanceOf($stateMachine, StateMachineInterface::class);

            if (null !== $transition = $stateMachine->getTransitionToState($nextState)) {
                $stateMachine->apply($transition);
            }
        }
    }
}
