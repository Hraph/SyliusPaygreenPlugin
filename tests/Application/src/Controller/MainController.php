<?php

declare(strict_types=1);

namespace Tests\Hraph\SyliusPaygreenPlugin\App\Controller;


use Doctrine\Common\Persistence\ObjectManager;
use Hraph\SyliusPaygreenPlugin\Entity\PaygreenShop;
use Hraph\SyliusPaygreenPlugin\Exception\PaygreenException;
use Hraph\SyliusPaygreenPlugin\Factory\PaygreenShopFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $manager;

    /**
     * MainController constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function createShopAction(Request $request): Response {
        $factory = new PaygreenShopFactory();
        $shop = $factory->create();
        $shop->setName("Test shop");
        $shop->setDescription("Test shop");
        $shop->setCompanyType("COMPANY");
        $shop->setBusinessIdentifier("12345678912345");
        $shop->setUrl("https://test.com");


        try {
            $this->manager->persist($shop);
        }
        catch (PaygreenException $exception)
        {
            dump($exception);
        }
        $this->manager->flush();
        return new Response();
    }
}
