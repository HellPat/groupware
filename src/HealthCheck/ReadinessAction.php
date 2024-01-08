<?php

namespace App\HealthCheck;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/.well-known/ready', methods: ['GET'])]
final class ReadinessAction
{
    public function __invoke(): Response
    {
        return new Response('Ready', Response::HTTP_OK);
    }
}
