<?php


namespace Hraph\SyliusPaygreenPlugin\Payum\Action;


use Hraph\SyliusPaygreenPlugin\Entity\PaygreenTransferInterface;
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

class ConvertTransferAction extends BaseApiGatewayAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    /**
     * ConvertPaymentAction constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }


    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaygreenTransferInterface $transfer */
        $transfer = $request->getSource();

        $details = [
            'amount' => $transfer->getAmount(),
            'currency' => $transfer->getCurrency(),
            'shop_id' => $transfer->getShopId(),
            'metadata' => [
                'transfer_id' => $transfer->getId()
            ],
        ];

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
