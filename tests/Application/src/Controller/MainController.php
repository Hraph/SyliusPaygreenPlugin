<?php

declare(strict_types=1);

namespace Tests\Hraph\SyliusPaygreenPlugin\App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenShopFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * MainController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
        return new Response();
    }
}
