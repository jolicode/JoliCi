# The run command

The run command is the main command of JoliCi. It create and prepare the differents environments (jobs) and execute the command on each of it.

## Workflow

### Creating environments

An environment is created when a build strategy is detected on your project, 
this can be as simple as [parsing a `.travis.yml`](../strategies/TravisCiStrategy.md) file or more complex
by [reading a `.jolici directory](../strategies/JoliCiStrategy.md)

Each environment will be prepared in a temporary directory so this will never modify your project (no need to add a line in 
your .gitignore file)

### Building environment

An environment is simply a docker image build with a Dockerfile, which can be generated for you when using
TravisCi strategy or using your own with JoliCi strategy

### Starting services

Before running test, JoliCi will try to launch services determined by your configuration file (only on TravisCi for the moment), 
in order to have mysql, memcached, elasticsearch, .... services available for your tests.

Each service will start from a clean state, data is not keeped.

### Running test

Once the environment is ready, JoliCi will run your test command on it and display the output directly on your console.

### Exit code

If all test on each environment is successful (return 0 for exit code), jolici will return as well 0 for exit code.
Otherwise it will return the number of failing tests.

### Cleaning

JoliCi try to do his best to keep disk space as low as possible without decreasing speed. In order to do this it will delete all
files related to an environment at the exception of the last run so we can use his cache.

## Default command:

```bash
php jolici.phar run
```

THe default command will run test for each environments created, this will only output the result of the test command. If you 
want to see the output of the build process (like TravisCi) you can run this command with a verbose option:

```bash
php jolici.phar run -v
```

## Options

Here is a list of options you can pass to the clean command:

* `--project-path DIRECTORY` / `-p DIRECTORY`: Set the root path (DIRECTORY) of your project, use current directory as a default
* `--keep NUMBER` / `-k NUMBER`: How many versions (NUMBER) of images, containers and / or build directories should be clean after running test (default to 1)
* `--no-cache`: Use this option if you don't want to use the cache from the last build
* `--timeout TIMEOUT` / `-t TIMEOUT`: This is the timeout, in seconds, for the run command, it allows to aborting test if a command hangs up forever (default to 5 minutes)

## Overriding test command

If one more argument is present there will be considered as the command line for running test, 
for example to see the php version of an environment created by JoliCi you can do the following command:

```bash
php jolici.phar run php -v
```
