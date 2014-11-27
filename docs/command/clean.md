# The clean command

JoliCi try to keep the number of docker images, containers and build directories as low as possible when running test.
 
But sometimes thing can break hard and cleaning process is not run, so you will need to run this command in order 
to not overflow your hard drive.

## Default command:

```
php jolici.phar clean
```

THe default command will clean all images, containers and build directories except for the last one, in the current project directory.

## Options

Here is a list of options you can pass to the clean command:

* `--project-path DIRECTORY` / `-p DIRECTORY`: Set the root path (DIRECTORY) of your project, use current directory as a default
* `--keep NUMBER` / `-k NUMBER`: How many versions (NUMBER) of images, containers and / or build directories should the clean process keeps
* `--only-containers`: Remove only containers
* `--only-directories`: Remove only build directories
* `--only-images`: Remove only images
* `--force`: Force removal of images

