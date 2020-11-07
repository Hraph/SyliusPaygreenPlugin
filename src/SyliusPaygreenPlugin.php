<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin;

use Hraph\SyliusPaygreenPlugin\DependencyInjection\SyliusPaygreenPluginExtension;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusPaygreenPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SyliusPaygreenPluginExtension();
    }


}
