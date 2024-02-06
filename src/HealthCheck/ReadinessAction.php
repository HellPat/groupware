<?php

namespace App\HealthCheck;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/.well-known/ready', methods: ['GET'])]
final class ReadinessAction
{
    public function __invoke(
        Connection $connection,
        LoggerInterface $logger,
    ): Response {
        try {
            $connection->executeQuery('SELECT 1');

            return new Response('Ready', Response::HTTP_OK);
        } catch (Exception $e) {
            $logger->debug('Database is not ready', ['exception' => $e]);

            return new Response(null, Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}
