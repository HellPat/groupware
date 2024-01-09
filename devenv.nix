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
        xdebug.mode=off
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
    process.implementation = "process-compose";
    processes.symfony.process-compose = {
      command = "rr serve -p -c .rr.dev.yaml --debug";
      availability = {
        backoff_seconds = 2;
        max_restarts = 5;
        restart = "always";
      };
      depends_on = {
        mysql = {
            condition = "process_started";
        };
        redis = {
            condition = "process_started";
        };
      };
      readiness_probe = {
        http_get = {
          port = 8000;
          path = "/.well-known/ready";
        };
      };
  };
  processes.symfony-message-consumer.process-compose = {
        # TODO: rethink limits and restarts.
        #       I set a time limit and a limit of jobs to process, to easy using xdebug.
        #       Xdebug listening must be started in the IDE, and the long running process must be restarted to take effect.
        command = "symfony run --watch=config,src,templates,vendor bin/console messenger:consume async --limit=10 --time-limit=300 --no-interaction -vv";
        availability = {
          restart = "always";
        };
        depends_on = {
          symfony = {
              condition = "process_started";
          };
        };
    };
}
