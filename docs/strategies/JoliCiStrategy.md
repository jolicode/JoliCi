# JoliCiBuildStrategy

This strategy is based on a directory structure and Dockerfile, this is the most flexible strategy as you can whatever you want for environment.

## Usage

To create jobs the project **MUST** have a `.jolici` directory.

Each subdirectory is then considered as a different job, they **MUST** have a `Dockerfile` which will explain how to create the environment.

The test command is determined by the `CMD` or `ENTRYPOINT` keywords in the `Dockerfile`.

## Overriding files

You may want to have a different configuration file for each environment. In order to have this behavior all files under the job directory (`.jolici/my_job_environment`) will be 
copied, before creation, to the root directory of the project.

## Add source to the build

Due to the precedent behavior, the Dockerfile is now at the root of your project. To add the source code of your project in your job environment you 
can add this command inside your Dockerfile:

```
ADD . /project
```

Your project will then be available at the `/project` path in the environment.
