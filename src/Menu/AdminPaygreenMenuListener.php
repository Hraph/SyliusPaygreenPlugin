<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Menu;


use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminPaygreenMenuListener
{
    public function buildMenu(MenuBuilderEvent $menuBuilderEvent): void
    {
        $menu = $menuBuilderEvent->getMenu();

        $menu
            ->addChild('paygreen')
            ->setLabel('hraph_sylius_paygreen_plugin.ui.paygreen')
                ->addChild('paygreen_shops', [
                    'route' => 'hraph_sylius_paygreen_plugin_admin_paygreen_shop_index'
                ])
                ->setLabel('hraph_sylius_paygreen_plugin.ui.paygreen_shops')
                ->setLabelAttribute('icon', 'universal access');


    }
}
