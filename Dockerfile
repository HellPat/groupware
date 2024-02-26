FROM nixos/nix:latest AS builder
WORKDIR /app/
COPY shell.nix /app/shell.nix
RUN nix-shell

COPY composer /app/composer
COPY composer.json /app/composer.json
COPY composer.lock /app/composer.lock
RUN nix-shell --run "./composer install --no-scripts --no-cache --no-autoloader"

COPY package.json /app/package.json
COPY pnpm-lock.yaml /app/pnpm-lock.yaml
RUN nix-shell --run "pnpm install --frozen-lockfile"

