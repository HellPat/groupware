develop:
    devenv up

lint:
    vendor/bin/ecs
    vendor/bin/phpstan
    
fix:
    vendor/bin/ecs --fix