# JoliCiBuildStrategy

This strategy is based on a directory structure and Dockerfiles, this is the most flexible strategy as you can build whatever you want.

## Usage

To create builds the project **MUST** have a `.jolici` directory.

Each subdirectory is then considered as a different build, they **MUST** have a `Dockerfile` which will explain how to build projects and how to run tests.

`CMD` or `ENTRYPOINT` commands in a `Dockerfile` will describe how to run a build.

## Add source to the build

For each build, this strategy will create a new directory with the content of project. Then it will recursively copy the content of build directory to the root of the new project directory. 

Since the Dockerfile is now at the root of project, the following line will add source to the build :

```
ADD . /project
```

Source code will then be available at the `/project` path in the container.

## Example

JoliCi is tested with JoliCi by using this strategy, look at the source in the `.jolici` directory of this repository if you want to see an example.