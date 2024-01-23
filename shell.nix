{
    # Pinning packages with URLs inside a Nix expression
    # https://nix.dev/tutorials/first-steps/towards-reproducibility-pinning-nixpkgs#pinning-packages-with-urls-inside-a-nix-expression
    # Picking the commit can be done via https://status.nixos.org,
    # which lists all the releases and the latest commit that has passed all tests.
    pkgs ? import (fetchTarball "https://github.com/NixOS/nixpkgs/archive/d7f206b723e42edb09d9d753020a84b3061a79d8.tar.gz") {},
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
    ];
    
    # TODO: configure dynamically to use the current directory
    MYSQL_HOME= "/home/pheller/Code/groupware/storage/mysql";
    MYSQL_DATADIR = "/home/pheller/Code/groupware/storage/mysql/data";
    MYSQL_UNIX_PORT = "/home/pheller/Code/groupware/.mysql.sock";
    MYSQLX_UNIX_PORT = "/home/pheller/Code/groupware/.mysqlx.sock";
    
    REDIS_DATADIR = "/home/pheller/Code/groupware/storage/redis/data";
    REDIS_PID = "/home/pheller/Code/groupware/.redis.pid";
    REDIS_SOCKET = "/home/pheller/Code/groupware/.redis.sock";
    
    shellHook = ''
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