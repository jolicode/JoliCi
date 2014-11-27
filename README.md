# JoliCi

JoliCi is a free and open source Continuous Integration _Client_ written in PHP (5.4 minimum) and powered by Docker (please use a recent version). It has been written to be compliant 
with existent Ci services like Travis-Ci and not create a new normalization. ([Remove that smile, i know what you're thinking.](http://xkcd.com/927/))

**This project is still in beta, there may be bugs and missing features**

[![Build Status](https://travis-ci.org/jolicode/JoliCi.png?branch=master)](https://travis-ci.org/jolicode/JoliCi) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/jolicode/JoliCi/badges/quality-score.png?s=1ba180546468c07ca8fc0996dcdc4a740dcf23fc)](https://scrutinizer-ci.com/g/jolicode/JoliCi/)

## Usage

[Have a .travis.yml in your project](http://docs.travis-ci.com/user/getting-started/#Step-three%3A-Add-.travis.yml-file-to-your-repository)

At this location [download last version of jolici](http://jolici.jolicode.com/jolici.phar) and run it:

```bash
wget http://jolici.jolicode.com/jolici.phar
php jolici.phar run
```

First run can be quite long since it has to build everything from the beginning. Subsequent build should be faster thanks to docker caching.

![JoliCi Demo](https://github.com/jolicode/JoliCi/raw/master/docs/jolici-terminal.gif "JoliCi Demo")

If you want to see what happens behind this black box:

```bash
wget http://jolici.jolicode.com/jolici.phar
php jolici.phar run -v
```

## Ci supported

* Travis-Ci
* [...][CONTRIBUTING.md)

## I want to read more

* [Installation](docs/installation.md)
* Usage
    * [The run command](docs/command/run.md)
    * [The clean command](docs/command/clean.md)
* Strategies (a.k.a. how to create a build from a configuration file)
    * [Travis-Ci](docs/strategies/TravisCiStrategy.md)
    * [JoliCi](docs/strategies/JoliCiStrategy.md)

## Credits

* [All contributors](https://github.com/jolicode/JoliCi/graphs/contributors)
* Some parts of this project are inspired by :
	* [Docker Client](https://github.com/dotcloud/docker/blob/master/commands.go)
* This README is heavily inspired by a @willdurand [blog post](http://williamdurand.fr/2013/07/04/on-open-sourcing-libraries/).
* [@ubermuda](https://github.com/ubermuda) for accepting many pull requests on [docker-php](https://github.com/stage1/docker-php) library

## License

View the [LICENSE](LICENSE) file attach to this project.
