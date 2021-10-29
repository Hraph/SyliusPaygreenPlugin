<?php

declare(strict_types=1);

namespace Tests\Hraph\SyliusPaygreenPlugin\App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenShopFactory;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenTransferFactory;
use Hraph\SyliusPaygreenPlugin\Payum\PaygreenGatewayFactory;
use Hraph\SyliusPaygreenPlugin\Payum\Request\Transfer;
use Hraph\SyliusPaygreenPlugin\Provider\GatewayConfigProvider;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Payum;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PaygreenTransferFactory $transferFactory;
    private Payum $payum;
    private RepositoryInterface $paymentMethodRepository;
    private GatewayConfigProvider $gatewayConfigProvider;
    private PaymentMethodFactoryInterface $paymentMethodFactory;

    public function __construct(EntityManagerInterface $entityManager,
                                RepositoryInterface  $paymentMethodRepository,
                                PaymentMethodFactoryInterface $paymentMethodFactory,
                                PaygreenTransferFactory $paygreenTransferFactory,
                                GatewayConfigProvider $gatewayConfigProvider,
                                Payum $payum)
    {
        $this->entityManager = $entityManager;
        $this->transferFactory = $paygreenTransferFactory;
        $this->payum = $payum;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->gatewayConfigProvider = $gatewayConfigProvider;
        $this->paymentMethodFactory = $paymentMethodFactory;
    }

    public function createShopAction(Request $request): Response {
        $factory = new PaygreenShopFactory();
        $shop = $factory->createNew();
        $shop->setName("Test shop");
        $shop->setDescription("Test shop");
        $shop->setCompanyType("COMPANY");
        $shop->setBusinessIdentifier("12345678912345");
        $shop->setUrl("https://test.com");

        try {
            $this->entityManager->persist($shop);
        }
        catch (PaygreenException $exception)
        {
            dump($exception);
        }
        $this->entityManager->flush();
        return new Response("Ok");
    }

    public function createTransferAction(Request $request): Response {
        $transfer = $this->transferFactory->createNew();
        $transfer->setAmount(1);
        $transfer->setCurrency("EUR");
        $transfer->setShopId("recipientShopId");

        $gatewayConfig = $this->gatewayConfigProvider->provideByFactoryName(PaygreenGatewayFactory::FACTORY_NAME);
        $gateway = $this->payum->getGateways()[$gatewayConfig->getGatewayName()];

        $gateway->execute(new Transfer($transfer));

        return new Response("Ok");
    }
}
