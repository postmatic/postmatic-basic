# Docker Development Environment

Develop using Docker Compose, no need to install PHP or MySQL.

## Install

A PHP service attempts to install Composer dependencies when the
development server is first brought up.

Alternatively, `./composer.sh install-test-support` will do the job.

## Development Server

To run MariaDB and WordPress with a live copy of Geo Mashup:

`docker-compose up`

This also runs a temporary php service to install PHP development
dependencies.

Control-C stops running services, or docker-compose stop if that goes awry.

## Testing

MariaDB must be running.

The test support framework must be installed once before running tests:

`./composer.sh install-test-support`

Tests can then be run as needed:

`./composer.sh test`

## Other tools

`composer.sh` is just a shorthand for a longer docker-compose command
which you can use if you prefer.
