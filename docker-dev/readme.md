# Docker Development Environment

Develop using Docker Compose, no need to install PHP or MySQL.

## Authorize

Create an `auth.json` file in this directory with an []access token](https://github.com/settings/tokens) that has
permission to read public repos.

```
{
  "http-basic": {
    "github.com": {
        "username": "<YOUR-USERNAME>",
        "password": "<TOKEN>"
    }
  }
}
```

## Install

A PHP service attempts to install Composer dependencies when the
development server is first brought up.

Alternatively, `./composer.sh install` will do the job.

## Development Server

To run MariaDB and WordPress with a live copy of Geo Mashup:

`docker-compose up`

This also runs a temporary composer service to install PHP development
dependencies.

Control-C stops running services, or docker-compose stop if that goes awry.

## Testing

MariaDB must be running.

The test support framework must be installed once before running tests:

`./composer.sh install-test-support`

Tests can then be run as needed:

`./phpunit.sh`

## Other tools

`composer.sh` is just a shorthand for a longer docker-compose command
which you can use if you prefer.
