# dump prod env
sudo -u www-data composer dump-env dev
# install composer packages
sudo -u www-data composer install --prefer-dist --optimize-autoloader --no-interaction
# clearing sf cache
rm -rf /var/www/ninjatooken/var/cache/dev
sudo -u www-data APP_ENV=dev APP_DEBUG=true php bin/console cache:warmup
# assets install
sudo -u www-data php bin/console assets:install
# clearing memcache
echo 'flush_all' | nc localhost 11211 | exit
# restart php services
service php7.4-fpm restart