<?php

namespace Joli\JoliCi;

use Docker\Docker;
use Docker\Http\DockerClient;
use Joli\JoliCi\Filesystem\Filesystem;
use Joli\JoliCi\Log\SimpleFormatter;
use Joli\JoliCi\BuildStrategy\TravisCiBuildStrategy;
use Joli\JoliCi\BuildStrategy\JoliCiBuildStrategy;
use Joli\JoliCi\Builder\DockerfileBuilder;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TwigGenerator\Builder\Generator;

class Container
{
    public function getTravisCiStrategy($projectPath)
    {
        $builder   = new DockerfileBuilder();
        $generator = new Generator();
        $generator->setTemplateDirs(array(
            __DIR__."/../../../resources/templates"
        ));
        $generator->setMustOverwriteIfExists(true);
        $generator->addBuilder($builder);

        return new TravisCiBuildStrategy($builder, $this->getBuildPath($projectPath), $this->getFilesystem($projectPath));
    }

    public function getJoliCiStrategy($projectPath)
    {
        return new JoliCiBuildStrategy($this->getBuildPath($projectPath), $this->getFilesystem($projectPath));
    }

    public function getConsoleLogger($verbose = false)
    {
        $logger               = new Logger("standalone-logger");
        $handler              = new StreamHandler("php://stdout", $verbose ? Logger::DEBUG : Logger::INFO);
        $stdErrHandler        = new StreamHandler("php://stderr", Logger::DEBUG);
        $fingerCrossedHandler = new FingersCrossedHandler($stdErrHandler, new ErrorLevelActivationStrategy(Logger::ERROR), 10);
        $simpleFormatter      = new SimpleFormatter();

        $handler->setFormatter($simpleFormatter);
        $stdErrHandler->setFormatter($simpleFormatter);
        $logger->pushHandler($handler);
        $logger->pushHandler($fingerCrossedHandler);

        return $logger;
    }

    public function getFilesystem($projectPath)
    {
        return new Filesystem($this->getBuildPath($projectPath));
    }

    public function getDocker($entryPoint = "unix:///var/run/docker.sock")
    {
        return new Docker(DockerClient::createWithEnv());
    }

    public function getExecutor($dockerEntryPoint, $cache = true, $verbose = false, $timeout = 600)
    {
        //Set timeout in ini (not superb but only way with current docker php library)
        ini_set('default_socket_timeout', $timeout);

        return new Executor($this->getConsoleLogger($verbose), $this->getDocker($dockerEntryPoint), $cache, false);
    }

    public function getBuildPath($projectPath)
    {
        return realpath($projectPath).DIRECTORY_SEPARATOR.".jolici-builds";
    }

    public function getBuilder($projectPath)
    {
        $builder = new Builder();
        $builder->pushStrategy($this->getJoliCiStrategy($projectPath));
        $builder->pushStrategy($this->getTravisCiStrategy($projectPath));

        return $builder;
    }
}