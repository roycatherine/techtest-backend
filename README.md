## About the Bid Calculation Tool

This is a coding challenge mvp and not a real product.
There is no user authentication. The vehicle's update and delete routes exist, but are not used by the frontend.

* Frontend : https://github.com/roycatherine/techtest-frontend

## Techs

* Docker
* PHP
* Laravel
* PHPUnit
* MySQL
* Redis

### Setup

[Docker](https://docs.docker.com/) is used for development environment. We use
[docker compose](https://docs.docker.com/compose/) to start everything you need to run the project.

### Installation

1. If not already done, install [Docker Desktop](https://www.docker.com/products/docker-desktop).
2. Run `./vendor/bin/sail up -d` in the project's directory to build the container and start all services.
3. Wait a few seconds for the MySQL service to start. Then run `./vendor/bin/sail artisan migrate` in the project's directory to run the migrations.
4. The API will be accessible on http://localhost/api. You must not have anything else running on port 80 (laravel), 3306 (mysql), or 6379 (redis).
5. Have fun!

### Unit tests

Run `./vendor/bin/phpunit` in the project's directory to run unit tests.
