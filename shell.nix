{
    pkgs ? import <nixpkgs> {},
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
        php
        pkgs.git
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
    ];
    
    GIT_EDITOR = "${pkgs.vim}/bin/vi";
    
    shellHook = ''
        git --version
        php -v
        composer --version
        symfony -V
        rr -v
        echo "pnpm version: " && pnpm -v
        git status
    '';
}