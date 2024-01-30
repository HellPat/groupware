{
    # Pinning packages with URLs inside a Nix expression
    # https://nix.dev/tutorials/first-steps/towards-reproducibility-pinning-nixpkgs#pinning-packages-with-urls-inside-a-nix-expression
    # Picking the commit can be done via https://status.nixos.org,
    # which lists all the releases and the latest commit that has passed all tests.
    pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/854f4671883250e456dc1553c783ac9741a0e9a4.tar.gz") {},
    php ? pkgs.php83.buildEnv {
      extensions = ({ enabled, all }: enabled ++ (with all; [
          redis
          openssl
          pcntl
          pdo_mysql
          mbstring
          intl
          curl
          bcmath
          apcu
          xdebug
      ]));
      extraConfig = ''
        xdebug.mode=develop,debug
        memory_limit=256M
      '';
    },
}:

pkgs.mkShell {
    buildInputs = [
        pkgs.vim
        pkgs.which
        pkgs.coreutils
        pkgs.overmind
        php
        pkgs.php83Packages.composer
        pkgs.git
        pkgs.openssh
        pkgs.jq
        pkgs.roadrunner
        pkgs.gh
        pkgs.gnupg
        pkgs.httpie
        pkgs.just
        pkgs.symfony-cli
        pkgs.nodePackages.pnpm
        pkgs.stripe-cli
        pkgs.tailwindcss
        pkgs.process-compose
        pkgs.mysql80
        pkgs.hostname
        pkgs.redis
        pkgs.envsubst
    ];
    
    shellHook = ''
        export OVERMIND_AUTO_RESTART=all
        export MYSQL_HOME=''${PWD}/storage/mysql
        export MYSQL_DATADIR=''${PWD}/storage/mysql/data
        export MYSQL_UNIX_PORT=''${PWD}/.mysql.sock
        export MYSQLX_UNIX_PORT=''${PWD}/.mysqlx.sock
        export REDIS_DATADIR=''${PWD}/storage/redis/data
        export REDIS_PID=''${PWD}/.redis.pid
        export REDIS_SOCKET=''${PWD}/.redis.sock
        export STRIPE_PROJECT_NAME=subscribe
        export STRIPE_DEVICE_NAME=developer-''${DEVELOPER_NAME:-default}
        source .env
        redis-server -v
        mysql --version
        git --version
        php -v
        composer --version
        symfony -V
        rr -v
        echo "pnpm version: " && pnpm -v
        git status
    '';
}