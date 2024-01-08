develop:
    devenv up

stripe-listen:
    # Write the Stripe signing secret to .env.local, to be used by the webhook handler.
    # TODO: this works but it's not ideal.
    #       The file is overwritten every time the command is run,
    #       we should instead add or replace only the STRIPE_SIGNING_SECRET line.
    echo "STRIPE_SIGNING_SECRET=$(stripe listen --print-secret)" > .env.local
    stripe listen -s --forward-to localhost:8000/stripe/webhook

lint:
    vendor/bin/ecs
    vendor/bin/psalm
    vendor/bin/phpstan
    
fix:
    vendor/bin/ecs --fix