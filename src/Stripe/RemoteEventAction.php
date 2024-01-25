<?php

namespace App\Stripe;

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
    ) {
    }

    public function __invoke(Request $request): Response
    {
        try {
            $this->messageBus->dispatch(RemoteEvent::fromRequest($request, $this->secret));
        } catch (SignatureVerificationException $e) {
            throw new BadRequestHttpException('Request does not match signature.', $e);
        }

        return new Response('', Response::HTTP_ACCEPTED);
    }
}
