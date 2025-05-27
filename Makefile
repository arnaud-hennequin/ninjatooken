build:
    docker-compose build --pull --no-cache

up:
    docker-compose up --detach

start: build up

down:
    docker-compose down --remove-orphans

logs:
    docker-compose logs --tail=0 --follow

sh:
    docker-compose exec --user www-data php sh