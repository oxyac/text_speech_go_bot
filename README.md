# Symfony Docker

A [Docker](https://www.docker.com/)-based installer and runtime for the [Symfony](https://symfony.com) web framework,
with [FrankenPHP](https://frankenphp.dev) and [Caddy](https://caddyserver.com/) inside!

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull -d --wait` to start the project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

SERVER_NAME=:80 \
APP_SECRET=lfdsadf21*%72kdgjlksdfjgwkjrlkr3jrljlkj \
HTTPS_PORT=4442 \
HTTP3_PORT=4442 \
HTTP_PORT=82 \
SQLITE_FOLDER_PATH=/data/voice.sqlite \
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --wait --build



docker-compose up --build\
service supervisorctl start\
composer install\
php artisan migrate\
php artisan db:seed --class=LanguageSeeder\
php vendor/bin/phpstan.phar analyse app\
supervisorctl update

**supervisor conf**

/etc/supervisor/conf.d/text_speech_go_bot.conf\
[program:text_speech_go_bot]\
process_name=%(program_name)s_%(process_num)02d\
command=php /var/www/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600\
autostart=true\
autorestart=true\
stopasgroup=true\
killasgroup=true\
user=root\
numprocs=2\
redirect_stderr=true\
stdout_logfile=/var/log/text_speech_go_bot.log\
stopwaitsecs=3600


/etc/supervisor/conf.d/text_speech_go_bot_worker.conf\
[program:text_speech_go_bot_worker]\
process_name=%(program_name)s_%(process_num)02d\
command=php /var/www/artisan telegram:update text_speech_go_bot\
autostart=true\
autorestart=true\
stopasgroup=true\
killasgroup=true\
user=root\
numprocs=1\
redirect_stderr=true\
stdout_logfile=/var/log/text_speech_go_bot_worker_worker.log\
stopwaitsecs=3600\
