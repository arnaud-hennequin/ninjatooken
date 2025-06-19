build:
	docker-compose build --pull --no-cache

up:
	docker-compose up --detach

start: build up

down:
	docker-compose down --remove-orphans

prune:
	docker system prune -a

logs:
	docker-compose logs --tail=0 --follow

sh:
	docker-compose exec --user www-data php sh

phpunit:
	docker-compose exec --user www-data php bin/phpunit

cs-fixer:
	docker-compose exec --user www-data php vendor/bin/php-cs-fixer fix

phpstan:
	docker-compose exec --user www-data php vendor/bin/phpstan

composer:
	docker-compose exec --user www-data php composer
