<?php

namespace App\Stripe;

use Stripe\Exception\SignatureVerificationException;
use Stripe\WebhookSignature;
use Symfony\Component\HttpFoundation\Request;

final readonly class RemoteEvent
{
    private function __construct(
        public \DateTimeImmutable $recordedAt,
        public string $type,
        public string $payload,
    ) {
    }

    /**
     * @throws SignatureVerificationException
     */
    public static function fromRequest(Request $request, string $secret): self
    {
        WebhookSignature::verifyHeader(
            $request->getContent(),
            (string) $request->headers->get('Stripe-Signature'),
            $secret
        );

        return new self(
            new \DateTimeImmutable(),
            (string) $request->getPayload()->get('type'),
            $request->getContent(),
        );
    }
}
