# How to contribute to Postmatic

Glad you're here! Coordinating a project requires practices that will work together. Those are described here. They are the dull scaffolding we build on, but should leave room for you to add your personal touch to Postmatic. 

## Follow Standards

Our goal is to adopt existing standards where possible:

* [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/)
* [PSR-4 Autoloader](http://www.php-fig.org/psr/psr-4/) Note we break WordPress file naming convention in favor of this.

## Set up a development environment

Start with a development environment meeting [WordPress requirements](https://wordpress.org/about/requirements/). 

[Install Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx) if you don't have it yet.

Clone the repository. Do this in `wp-content/plugins` if you're working in a default WordPress root.

	$ git clone git@githum.com:postmatic/postmatic-basic.git
	$ cd postmatic-basic
	
There will probably be a development branch. You'll probably want to create a feature branch from that.

	$ git checkout x.x.x-dev
	$ git checkout -b my-feature
	
Use composer to install dependencies and build autoloaders.

	$ composer install

If you have a [WordPress core SVN checkout](https://develop.svn.wordpress.org/trunk) somewhere, set an environment variable to point to it.

	$ export WP_DEVELOP_DIR=/home/me/wp-dev
	
Alternatively you can run a script to install a WordPress development instance in /tmp with your database information. Be aware that *THIS DATABASE WILL BE DESTROYED* every time tests are run.

	$ ./vendor/frozzare/wp-test-suite/bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
	
You should now be able to run the tests.

	$ ./vendor/bin/phpunit
	
## Write tests

The `tests` directory contains all tests. Add tests for your classes and methods accordingly.

## Keep our API stable

The `Prompt_Api` class should always be [fully documented](http://docs.gopostmatic.com/collection/103-api-developer-docs) and backward compatible within minor versions.

## Submit pull requests

The [github collaboration docs](https://help.github.com/categories/collaborating-on-projects-using-issues-and-pull-requests/) on forking and submitting pull requests should be sufficient.

