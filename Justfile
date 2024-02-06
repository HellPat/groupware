start-background:
    nohup process-compose up -t=false > /dev/null 2>&1 &

stop:
    process-compose down

develop:
    process-compose up

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
    ./composer install
    bin/console cache:warmup

setup-frontend:
    #!/usr/bin/env bash
    set -euxo pipefail
    pnpm install --frozen-lockfile
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
    pnpm exec prettier . --check
    vendor/bin/rector --dry-run
    vendor/bin/parallel-lint --exclude .git --exclude var --exclude vendor .

fix:
    vendor/bin/ecs --fix
    just --fmt --unstable
    pnpm exec prettier . --write
    vendor/bin/rector
