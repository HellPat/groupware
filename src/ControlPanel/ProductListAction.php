<?php

namespace App\ControlPanel;

use App\Stripe\MysqlProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductListAction extends AbstractController
{
    // TODO: should be about products
    public function __invoke(MysqlProductRepository $products): Response
    {
        return $this->render('product/list.html.twig', [
            'customers' => $products->all(),
        ]);
    }
}
