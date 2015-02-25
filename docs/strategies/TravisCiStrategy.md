# TravisCiBuildStrategy

This strategy parses the .travis.yml file at the root of your project to create a Dockerfile for each job and run tests.

## Language and version support

For the moment only the following language / version list is supported, the goal is to support what travis supports:

* php
	* 5.3 (5.3.28)
	* 5.4 (5.4.31)
	* 5.5 (5.5.15)
	* 5.6 (5.6.3)
	* hhvm (3.3.0)
* ruby
    * 1.9.3
    * 2.0.0
    * 2.1.0
* node
    * 0.6
    * 0.8
    * 0.10
    * 0.11

## Service support

Some services are supported for you to use

## Docker images

This strategy use pre-built docker images available on this github repository: [https://github.com/jolicode/docker-images](https://github.com/jolicode/docker-images)

