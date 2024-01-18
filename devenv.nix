{ pkgs, ... }:

{
    # Dotenv integration did not works, so I did this manually in `enterShell`.
    #dotenv.enable = true;
    dotenv.disableHint = true;
    
    difftastic.enable = true;

    # https://devenv.sh/packages/
    packages = [ 
      pkgs.git
      pkgs.jq
      pkgs.roadrunner
      pkgs.gh
      pkgs.gnupg
      pkgs.httpie
      pkgs.just
      pkgs.symfony-cli
      pkgs.coreutils
      pkgs.phpPackages.phive # Not used now, but we might use it to install phpunit and others.
      pkgs.nodePackages.pnpm # not used now, but we might use it to install tailwindcss and others.
      pkgs.stripe-cli
      pkgs.tailwindcss
    ];
    
    enterShell = ''
        source .env
        echo ".env loaded"
        jq --version
        php --version
    ''; 
    
    # https://devenv.sh/languages/
    # languages.nix.enable = true;
    
    # https://devenv.sh/pre-commit-hooks/
    # pre-commit.hooks.shellcheck.enable = true;
    
    # https://devenv.sh/processes/
    # processes.ping.exec = "ping example.com";
    
    # See full reference at https://devenv.sh/reference/options/
  
    languages.php = {
      enable = true;
      version = "8.3";
      extensions = [ "xdebug" "pcntl" "redis" "apcu" ];
    
      ini = ''
        session.cookie_httponly = 1
        memory_limit = 256m
        xdebug.mode=debug,develop
        xdebug.start_with_request=yes
      '';
    };
    
    # https://devenv.sh/services/#supported-services
    # https://devenv.sh/reference/options/#servicesmysqlenable
    services.mysql = {
      enable = true;
      package = pkgs.mysql80;
      initialDatabases = [
          { name = "app"; }
          { name = "test"; }
      ];
      ensureUsers = [
          {
            name = "app"; 
            password = "app";
            ensurePermissions = {
              "app.*" = "ALL PRIVILEGES";
            };
          }
      ];
      settings = {
        mysqld = {
            port = "13309";
        };
      };
    };
    
    services.redis = {
      enable = true;
      port = 16379;
    };
    
    # https://devenv.sh/processes/
    process.implementation = "overmind";
    # Write the Stripe signing secret to .env.local, to be used by the webhook handler.
    # TODO: this works but it's not ideal.
    #       The file is overwritten every time the command is run,
    #       we should instead add or replace only the STRIPE_SIGNING_SECRET line.
    #
    #       An other solution could be, to use an API-Token instead.
    process.before = "echo \"STRIPE_SIGNING_SECRET=$(stripe listen --print-secret)\" > .env.local";
    processes = {
        symfony.exec = "rr serve -p -c .rr.dev.yaml --debug";
        # TODO: rethink limits and restarts.
        #       I set a time limit and a limit of jobs to process, to easy using xdebug.
        #       Xdebug listening must be started in the IDE, and the long running process must be restarted to take effect.
        symfony-message-consumer.exec = "symfony run --watch=config,src,vendor bin/console messenger:consume async --limit=10 --time-limit=300 --no-interaction -vv";
        stripe.exec = "stripe listen --skip-verify --forward-to localhost:8000/webhook/stripe";
        tailwindcss.exec = "tailwindcss -i assets/styles/app.css -o assets/styles/app.tailwind.css --watch";
    };
}
