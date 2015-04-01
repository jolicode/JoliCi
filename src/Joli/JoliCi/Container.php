<?php

namespace Joli\JoliCi;

use Docker\Docker;
use Docker\Http\DockerClient;
use Joli\JoliCi\BuildStrategy\ChainBuildStrategy;
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
    private $docker;

    /**
     * Strategy based on the ".travis.yml" file
     *
     * @return TravisCiBuildStrategy
     */
    public function getTravisCiStrategy()
    {
        $builder   = new DockerfileBuilder();
        $generator = new Generator();
        $generator->setTemplateDirs(array(
            __DIR__."/../../../resources/templates",
        ));
        $generator->setMustOverwriteIfExists(true);
        $generator->addBuilder($builder);

        return new TravisCiBuildStrategy($builder, $this->getBuildPath(), $this->getNaming(), $this->getFilesystem());
    }

    /**
     * Strategy based on the ".jolici" folder
     *
     * @return JoliCiBuildStrategy
     */
    public function getJoliCiStrategy()
    {
        return new JoliCiBuildStrategy($this->getBuildPath(), $this->getNaming(), $this->getFilesystem());
    }

    /**
     * Chain strategy to allow multiples ones
     *
     * @return ChainBuildStrategy
     */
    public function getChainStrategy()
    {
        $strategy = new ChainBuildStrategy();
        $strategy->pushStrategy($this->getTravisCiStrategy());
        $strategy->pushStrategy($this->getJoliCiStrategy());

        return $strategy;
    }

    /**
     * Alias for the main strategy
     *
     * @return \Joli\JoliCi\BuildStrategy\BuildStrategyInterface
     */
    public function getStrategy()
    {
        return $this->getChainStrategy();
    }

    /**
     * Get a console with finger crossed handler
     *
     * @param bool $verbose
     *
     * @return Logger
     */
    public function getConsoleLogger($verbose = false)
    {
        $logger               = new Logger("standalone-logger");
        $handler              = new StreamHandler("php://stdout", $verbose ? Logger::DEBUG : Logger::INFO);
        $simpleFormatter      = new SimpleFormatter();

        $handler->setFormatter($simpleFormatter);
        $logger->pushHandler($handler);

        if (!$verbose) {
            $stdErrHandler = new StreamHandler("php://stderr", Logger::DEBUG);
            $fingerCrossedHandler = new FingersCrossedHandler($stdErrHandler, new ErrorLevelActivationStrategy(Logger::ERROR), 10);

            $logger->pushHandler($fingerCrossedHandler);
            $stdErrHandler->setFormatter($simpleFormatter);
        }

        return $logger;
    }

    public function getVacuum()
    {
        return new Vacuum($this->getDocker(), $this->getNaming(), $this->getStrategy(), $this->getFilesystem(), $this->getBuildPath());
    }

    public function getFilesystem()
    {
        return new Filesystem();
    }

    public function getDocker()
    {
        if (!$this->docker) {
            $this->docker = new Docker(DockerClient::createWithEnv());
        }

        return $this->docker;
    }

    public function getExecutor($cache = true, $verbose = false, $timeout = 600)
    {
        return new Executor($this->getLoggerCallback($verbose), $this->getDocker(), $this->getBuildPath(), $cache, false, $timeout);
    }

    public function getServiceManager($verbose = false)
    {
        return new ServiceManager($this->getDocker(), $this->getLoggerCallback($verbose));
    }

    public function getBuildPath()
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.".jolici-builds";
    }

    public function getNaming()
    {
        return new Naming();
    }

    public function getLoggerCallback($verbose)
    {
        return new LoggerCallback($this->getConsoleLogger($verbose));
    }
}
