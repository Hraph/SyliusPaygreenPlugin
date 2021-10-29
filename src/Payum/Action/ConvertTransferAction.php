<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Payum\Action;

use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
use Hraph\SyliusPaygreenPlugin\Payum\Action\Api\BaseApiGatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Psr\Log\LoggerInterface;

final class ConvertTransferAction extends BaseApiGatewayAwareAction implements ActionInterface
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaygreenTransferInterface $transfer */
        $transfer = $request->getSource();

        $details = [
            'amount' => $transfer->getAmount(),
            'currency' => $transfer->getCurrency(),
            'metadata' => [
                'transfer_id' => $transfer->getId()
            ],
        ];
        if (null !== $transfer->getShopId()) {
            $details['shop_id'] = $transfer->getShopId();
        }
        else if (null !== $transfer->getBankId()) {
            $details['bank_id'] = $transfer->getBankId();
        }

        // Set transfer to change API config depending on context
        $this->apiFactory->setTransferContextForConfigResolver($transfer);

        $request->setResult($details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaygreenTransferInterface &&
            $request->getTo() === 'array'
            ;
    }
}
