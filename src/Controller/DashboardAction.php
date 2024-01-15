<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', methods: [Request::METHOD_GET])]
final class DashboardAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('dashboard.html.twig');
    }
}
