<?php

namespace App\ControlPanel;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem('knp_menu.menu')]
final readonly class Menu
{
    public function __construct(private FactoryInterface $factory)
    {
    }

    public function main(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', $options);

        $menu->addChild('menu.dashboard', ['route' => DashboardAction::class]);
        $menu->addChild('menu.customers', ['route' => CustomerListAction::class]);

        return $menu;
    }
}
