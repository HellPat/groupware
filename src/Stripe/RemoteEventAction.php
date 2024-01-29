<?php

namespace App\Stripe;

use Psr\Log\LoggerInterface;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
final readonly class RemoteEventAction
{
    public function __construct(
        #[Autowire(env: 'STRIPE_SIGNING_SECRET')]
        #[\SensitiveParameter]
        private string              $secret,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $event = RemoteEvent::fromRequest($request, $this->secret);
            $this->logger->debug('Received Stripe event of type "{type}"', [
                'type' => $event->type,
                'event' => $event->payload
            ]);
            $this->messageBus->dispatch($event);
        } catch (SignatureVerificationException $e) {
            throw new BadRequestHttpException('Request does not match signature.', $e);
        }

        return new Response('', Response::HTTP_ACCEPTED);
    }
}
