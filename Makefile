build.container.dev:
	docker build -t auth-backend-php:dev .
	docker run -d --name auth-backend-php auth-backend-php:dev
	docker cp auth-backend-php:/var/www/vendor/. ./vendor
	docker cp auth-backend-php:/var/www/composer.lock ./composer.lock
	docker stop auth-backend-php
	docker rm auth-backend-php
	docker-compose up -d

build-dev: build.container.dev

up:
	docker-compose up -d

down:
	docker-compose down
