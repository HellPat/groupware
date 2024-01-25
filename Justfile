start-background:
    overmind start -D

develop:
    overmind start

setup:
    just setup-backend
    just setup-database
    just setup-frontend
    just setup-stripe

setup-database:
    #!/usr/bin/env bash
    set -euxo pipefail
    ${MYSQL_HOME}/init.sh

setup-backend:
    #!/usr/bin/env bash
    set -euxo pipefail
    composer install --ignore-platform-req=ext-redis
    bin/console cache:warmup

setup-frontend:
    #!/usr/bin/env bash
    set -euxo pipefail
    tailwindcss -i assets/styles/app.css -o assets/styles/app.tailwind.css

setup-stripe:
    #!/usr/bin/env bash
    set -euxo pipefail
    # TODO: secret should not rely on a file
    echo "STRIPE_SIGNING_SECRET=$(stripe listen --print-secret)" > .env.local

lint:
    vendor/bin/psalm
    vendor/bin/phpstan
    vendor/bin/ecs

fix:
    vendor/bin/ecs --fix
    just --fmt --unstable
