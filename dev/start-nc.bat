@echo off
docker-compose down
docker-compose pull

docker-compose up --force-recreate