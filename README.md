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
