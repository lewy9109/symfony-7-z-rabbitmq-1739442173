#!/usr/bin/env bash

BASEDIR=$(pwd)
echo "Working directory: $BASEDIR"
cp -n "$BASEDIR/.env.dist" "$BASEDIR/.env"


docker-compose stop
docker-compose build --no-cache
docker-compose up -d --remove-orphans

docker exec -it --user www-data app_php composer install

#docker exec -it --user www-data "${CONTAINER_NAME}" bin/console doctrine:database:create --if-not-exists
#docker exec -it --user www-data "${CONTAINER_NAME}" bin/console doctrine:migrations:migrate -n
#docker exec -it --user www-data "${CONTAINER_NAME}" bin/console doctrine:schema:validate
