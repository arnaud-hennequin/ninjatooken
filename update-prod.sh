# dump prod env
sudo -u www-data composer dump-env prod
# install composer packages
sudo -u www-data composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs
# clearing sf cache
rm -rf /var/www/ninjatooken/var/cache/prod
sudo -u www-data APP_ENV=prod APP_DEBUG=false php bin/console cache:warmup
# assets install
sudo -u www-data php bin/console assets:install
# clearing memcache
echo 'flush_all' | nc localhost 11211 | exit
# restart php services
service php8.1-fpm restart