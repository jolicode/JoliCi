# JoliCi

JoliCi is a free and open source Continuous Integration Client written in PHP and propulsed by Docker. It has been written to be easy to use.
Thanks to the use of Docker, all kind of projects can be tested with this CI (not just PHP) : you are completly free.

**This project is still in beta, there may be bugs and missing features**

## Features

* Secured and isolated build
* Multiple diffrents builds by project

## Usage

JoliCi need to know how to create builds from your project a.k.a. `BuildStrategy`. 

For the moment, only [JoliCiBuildStrategy](docs/strategies/JoliCiStrategy.md) is supported, but more strategy (like reading a .travis.yml file) will be supported in the future.

This strategies are determined by reading your project directory.

## Installation

### Requirements

* [Docker](http://docker.io) (a recent version is better and encouraged)
* PHP 5.4 at least

### Instructions

* Download `jolici.phar`
* Run it under your project `php jolici.phar run`

## Contributing

Here are a few rules to follow in order to ease code reviews, and discussions before maintainers accept and merge your work.

* You **MUST** follow the [PSR-1](http://www.php-fig.org/psr/1/) and [PSR-2](http://www.php-fig.org/psr/2/).
* You **MUST** run the test suite.
* You **MUST** write (or update) unit tests.
* You **SHOULD** write documentation.

Please, write [commit messages that make sense](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html), and [rebase your branch](http://git-scm.com/book/en/Git-Branching-Rebasing) before submitting your Pull Request.

One may ask you to [squash your commits](http://gitready.com/advanced/2009/02/10/squashing-commits-with-rebase.html) too. This is used to "clean" your Pull Request before merging it (we don't want commits such as `fix tests`, `fix 2`, `fix 3`, etc.).

Also, when creating your Pull Request on GitHub, you **MUST** write a description which gives the context and/or explains why you are creating it.

Thank you!

## Credits

* Some parts of this project are inspired by :
	* [Docker Client](https://github.com/dotcloud/docker/blob/master/commands.go)
* This README is heavily inspired by a @willdurand [blog post](http://williamdurand.fr/2013/07/04/on-open-sourcing-libraries/).
* [@ubermeda](https://github.com/ubermuda) for accepting many pull requests on [docker-php](https://github.com/stage1/docker-php) library

## Licence

View the [LICENCE](LICENCE) file attach to this project.