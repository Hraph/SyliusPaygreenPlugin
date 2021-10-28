<?php

namespace Hraph\SyliusPaygreenPlugin\Provider;

use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;

class GatewayConfigProvider
{

    private EntityRepository $gatewayConfigRepository;
    private PaymentMethodFactoryInterface $paymentMethodFactory;
    private PaymentMethodRepository $paymentMethodRepository;

    public function __construct(EntityRepository $gatewayConfigRepository, PaymentMethodFactoryInterface $paymentMethodFactory, PaymentMethodRepository $paymentMethodRepository)
    {
        $this->gatewayConfigRepository = $gatewayConfigRepository;
        $this->paymentMethodFactory = $paymentMethodFactory;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * @throws \Exception
     */
    public function provideByFactoryName(string $factoryName): GatewayConfigInterface
    {
        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $this->gatewayConfigRepository->findOneBy(["factoryName" => $factoryName]);

        if (null === $gatewayConfig) {
            $method = $this->paymentMethodFactory->createWithGateway($factoryName);
            $method->setCode($factoryName);
            $gatewayConfig = $method->getGatewayConfig();
            if (null !== $gatewayConfig) {
                $gatewayConfig->setGatewayName($factoryName);
            } else {
                throw new \Exception("New gateway config should not be null");
            }

            $this->paymentMethodRepository->add($method); // Persist
        }

        return $gatewayConfig;
    }
}
