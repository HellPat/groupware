<?php

namespace App\ControlPanel;

use App\Stripe\MysqlCustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/customers')]
final class CustomerListAction extends AbstractController
{
    public function __invoke(MysqlCustomerRepository $customers): Response
    {
        return $this->render('customer/list.html.twig', [
            'customers' => $customers->all(),
        ]);
    }
}
