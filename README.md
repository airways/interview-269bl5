# Reporting system exercise

## System design

This is a small PHP application running on v4 of the [Slim microframework](https://www.slimframework.com/docs/v4/), and
connected to MariaDB/MySQL.

Where possible, we have tried to use libraries and wire things up "manually" rather than use magical "bridge" libraries,
so that the flow of control will be clear regardless of whether you are familiar with this stack. There is a dependency
injection container used, but you will not need to understand how it works to complete the project; following the
pattern in the existing code should be enough.

The controllers render [Twig](https://twig.symfony.com/) templates found in the `templates/` directory.

### Key documentation pages for Slim

* [Routing](https://www.slimframework.com/docs/v4/objects/routing.html)
* [Request](https://www.slimframework.com/docs/v4/objects/request.html) and
  [Response](https://www.slimframework.com/docs/v4/objects/response.html) objects

## How to run

You are welcome to run the application on whatever platform you prefer. We have included a Docker-based setup for your
convenience.

### Docker setup

The database image comes with the structure and seed data preloaded.

> :warning: **WARNING:** If you remove the database container, any changes you've made to the database will be lost.

1. Use Composer to install dependencies, e.g.:

    ```shell
    docker run --rm -v $PWD:/app -v $HOME/.composer:/tmp composer:latest composer install --no-interaction --prefer-dist
    ```

2. Run the command `docker compose up` to launch the two containers that serve the application - one for the PHP web
   server, and one for the database server. By default, the web server port will be published to your host OS on port `8080`

#### Using a MySQL client

The database is accessible on port `13306`: you can do `mysql -h 127.0.0.1 -P 13306 -u root cpp_interview` or
`docker compose exec mysql mysql cpp_interview` to connect to the database running in the container.

#### Xdebug

Xdebug is included in the Docker build. Follow the instructions in your IDE running on your host OS, and you should be
able to set up the application in something like (in the case of PHPStorm) a "PHP Remote Debug" configuration.

### Custom setup

1. Use [Composer](https://getcomposer.org/) to load the project's dependencies
2. Point your web server running PHP 8.1 or greater at the `public/` directory. (e.g. `php -S 0.0.0.0:8080 -t public/`)
   All web requests (e.g. `http://localhost:8080/reports/payments`) should be handled by `public/index.php`
3. Update the MySQL configuration in `dbConnection.php` as appropriate for your setup
4. Load the database structure from `cpp_interview.schema.sql`
5. Load the seed data by running `scripts/seed.php`

### Running tests

Integration test for this project uses a separate copy of the full docker-compose project 
including a separate database instance.

You can run the integration test with:

   ./script/run-tests.php

This should be done outside of the container as it spins up a separate stack.

You may need to adjust the #! line in this script or just run it with:

   php ./script/run-tests.php

### Database Migrations

This project now has phpmig for migrations. Migrations are applied upon startup after the base
DB image seeds itself, ensuring each boot of the stack has a 100% creatable environment.

See phpmig documentation for more on how to create migrations:

   https://github.com/davedevelopment/phpmig

Note that this currently uses a plain text log file as the record of what migrations have been
applied. This needs to be updated to be a database backed adapter (see docs above), but for
time I have left it as the plain text storage. Files used for this purpose are under migrations/.migrations.log and migrations/.tests-migrations.log.

These files should not be checked into source control and need to be deleted any time the DB
is recreated. For now, the test scripts clear their own migration log and the Dockerfile clears
the migration file on startup. This might not be 100% accurate if you are only restarting the
web container.
