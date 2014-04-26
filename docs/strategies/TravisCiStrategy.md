# TravisCiBuildStrategy

This strategy parses the .travis.yml file at the root of your project to create a Dockerfile and run tests.

## Language and version support

For the moment only the following language / version list is supported, the goal is to support what travis supports:

* php
	* 5.3
	* 5.4
	* 5.5
* ruby
    * 1.9.2
    * 1.9.3
    * 2.0.0
    * 2.1.0
