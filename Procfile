# TODO: handle dependencies between services by using a wait-for.sh script.

mysql: mysqld --basedir=${MYSQL_HOME}/ --datadir=${MYSQL_DATADIR}/ --init-file=${MYSQL_HOME}/init.sql --skip-networking --default-time-zone=SYSTEM --log-error=${TMPDIR}/error.log --lc_messages_dir=${TMPDIR}/ --lc_messages=en_US --console
redis: envsubst < storage/redis/redis.conf | redis-server -
symfony: rr serve -p -c .rr.dev.yaml --debug

# TODO: rethink limits and restarts, when code changes, the process must be restarted.
#       I set a time limit and a limit of jobs to process, to easy using xdebug.
#       Xdebug listening must be started in the IDE, and the long running process must be restarted to take effect.
symfony-message-consumer: bin/console messenger:consume async --no-interaction -vv

stripe: stripe listen --skip-verify --forward-to localhost:8000/webhook/stripe
tailwindcss: tailwindcss -i assets/styles/app.css -o assets/styles/app.tailwind.css --watch