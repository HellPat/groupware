<?php

namespace App\Stripe;

use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;

final class RequestParser extends AbstractRequestParser
{
    public function __construct(
        #[Autowire(env: 'STRIPE_SIGNING_SECRET')]
        #[\SensitiveParameter]
        private readonly string $secret
    ) {
    }

    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new MethodRequestMatcher('POST'),
            // new IpsRequestMatcher(['3.134.147.250', '50.31.156.6', '50.31.156.77', '18.217.206.57', '127.0.0.1', '::1']),
            // new IsJsonRequestMatcher(),
        ]);
    }

    protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->headers->get('Stripe-Signature'),
                $this->secret
            );

            return new RemoteEvent($event->type, $event->id, $request->getPayload()->all());
        } catch (SignatureVerificationException $e) {
            throw new RejectWebhookException(Response::HTTP_BAD_REQUEST, 'Request does not match.', $e);
        }
    }
}
