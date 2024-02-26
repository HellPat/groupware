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

    /**
     * @param array<string, mixed> $options
     */
    public function main(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', $options);

        $menu->addChild('menu.dashboard', ['route' => DashboardAction::class]);
        
        $management = $menu->addChild('menu.management', ['route' => DashboardAction::class]);
        $management->addChild('menu.customers', ['route' => 'customer_list', 'routes' => ['customer_list']]);
        $management->addChild('menu.products', ['route' => ProductListAction::class]);

        return $menu;
    }
}
