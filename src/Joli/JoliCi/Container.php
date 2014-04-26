<?php

namespace Joli\JoliCi;

use Docker\Docker;
use Docker\Http\Client;
use Joli\JoliCi\Log\SimpleFormatter;
use Joli\JoliCi\BuildStrategy\TravisCiBuildStrategy;
use Joli\JoliCi\BuildStrategy\JoliCiBuildStrategy;
use Joli\JoliCi\Builder\DockerfileBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use TwigGenerator\Builder\Generator;

class Container
{
    public function getTravisCiStrategy()
    {
        $builder   = new DockerfileBuilder();
        $generator = new Generator();
        $generator->setTemplateDirs(array(
            __DIR__."/../../../resources/templates"
        ));
        $generator->setMustOverwriteIfExists(true);
        $generator->addBuilder($builder);

        return new TravisCiBuildStrategy($builder, $this->getBuildPath());
    }

    public function getJoliCiStrategy()
    {
        return new JoliCiBuildStrategy($this->getBuildPath());
    }

    public function getConsoleLogger($verbose = false)
    {
        $logger  = new Logger("standalone-logger");
        $handler = new StreamHandler("php://stdout", $verbose ? Logger::DEBUG : Logger::INFO);

        $handler->setFormatter(new SimpleFormatter());
        $logger->pushHandler($handler);

        return $logger;
    }

    public function getDocker($entryPoint = "unix:///var/run/docker.sock")
    {
        return new Docker(new Client($entryPoint));
    }

    public function getExecutor($dockerEntryPoint, $cache = true, $quiet = true, $timeout = 600)
    {
        //Set timeout in ini (not superb but only way with current docker php library)
        ini_set('default_socket_timeout', $timeout);

        return new Executor($this->getConsoleLogger(!$quiet), $this->getDocker($dockerEntryPoint), $cache, $quiet);
    }

    public function getBuildPath()
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR."jolici-builds";
    }

    public function getBuilder()
    {
        $builder = new Builder();
        $builder->pushStrategy($this->getJoliCiStrategy());
        $builder->pushStrategy($this->getTravisCiStrategy());

        return $builder;
    }
}