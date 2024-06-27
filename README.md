# Asterisk-Dev - Est #

## Requirements ##

- Git
- Docker

---

## Start ##

```bash
cd docker
docker-compose up --build

# Wait until `ready to handle connections` notice appears
```

## Start in production ##

```bash
cd docker
# Use detached mode in production
docker-compose up --build -d
# Wait until `ready to handle connections` notice appears

# Stoping detached containers
docker-compose down
```

If running for the first time:

```bash
# Stop docker if running

# Create .env configuration file from example
cp .env_example .env

# Create SSL certificates (or use DEV copy - see next point)
openssl req -x509 -nodes -days 365 -subj "/C=PL/ST=Mazowieckie/O=APK/CN=est.pl" -addext "subjectAltName=DNS:est.pl" -newkey rsa:2048 -keyout ./nginx/ssl/private/private.key -out ./nginx/ssl/certs/public.crt

# (Optionally) Copy SSL certs for localhost
cp ./nginx/ssl_dev/certs/public.crt ./nginx/ssl/certs/public.crt
cp ./nginx/ssl_dev/private/private.key ./nginx/ssl/private/private.key

# Edit this file accordingly to your environment
# Change default passwords and secrets !!!

# Update db schema
docker-compose run --rm php-fpm php bin/console doctrine:migrations:migrate

# For production load only init fixtures
docker-compose run --rm php-fpm php bin/console doctrine:fixtures:load

# Start docker again
```
## To clear cache run: ##

sudo rm -f -r symfony/var/cache/prod/*

sudo docker-compose run --rm php-fpm php bin/console cache:pool:clear cache.global_clearer

sudo docker-compose run --rm php-fpm php bin/console cache:pool:clear cache.app_clearer

---

## Services ##

Symfony App: [localhost](localhost:80) **DEV** credentials: (*admin/admin*)

PMA: [localhost:8080](localhost:8080) **DEV** credentials: (*root/secret*)

---

## DEV ##

Use **migrations** instead of `doctrine:schema:update --force`

- Create migration:

```bash
docker-compose run --rm php-fpm php bin/console make:migration
```

- Migrate:

```bash
docker-compose run --rm php-fpm php bin/console doctrine:migrations:migrate
```