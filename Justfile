develop:
    devenv up

build:
    tailwindcss -i assets/styles/app.css -o assets/styles/app.tailwind.css

rebuild:
    bin/console d:d:d --force
    bin/console d:d:c --no-interaction
    bin/console d:m:m --no-interaction
    bin/console d:s:u --complete --dump-sql
    just build

stripe-listen:
    # Write the Stripe signing secret to .env.local, to be used by the webhook handler.
    # TODO: this works but it's not ideal.
    #       The file is overwritten every time the command is run,
    #       we should instead add or replace only the STRIPE_SIGNING_SECRET line.
    #
    #       An other solution could be, to use an API-Token instead.
    echo "STRIPE_SIGNING_SECRET=$(stripe listen --print-secret)" > .env.local
    stripe listen --skip-verify --forward-to localhost:8000/webhook/stripe

lint:
    vendor/bin/psalmsymfony nix-shell
    vendor/bin/phpstan
    vendor/bin/ecs
    
fix:
    vendor/bin/ecs --fix