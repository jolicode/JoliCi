<?php

namespace Joli\JoliCi;

use Docker\Container as DockerContainer;
use Docker\Docker;
use Docker\Exception\ImageNotFoundException;
use Docker\Exception\UnexpectedStatusCodeException;
use Psr\Log\LoggerInterface;

class ServiceManager
{
    private $docker;

    private $logger;

    public function __construct(Docker $docker, LoggerCallback $logger)
    {
        $this->docker = $docker;
        $this->logger = $logger;
    }

    /**
     * Start services for a Job
     *
     * @param Job $build
     */
    public function start(Job $build)
    {
        foreach ($build->getServices() as $service) {
            try {
                $image = $this->docker->getImageManager()->find($service->getRepository(), $service->getTag());
            } catch (ImageNotFoundException $e) {
                $image = $this->docker->getImageManager()->pull($service->getRepository(), $service->getTag(), $this->logger->getBuildCallback());
            }

            $container = new DockerContainer($service->getConfig());
            $container->setImage($image);
            $service->setContainer($container);

            $this->docker->getContainerManager()->run($container, null, array(), true);
        }
    }

    /**
     * Stop services for a Job and reinit volumes
     *
     * @param Job $build
     * @param int $timeout
     *
     * @throws \Docker\Exception\UnexpectedStatusCodeException
     */
    public function stop(Job $build, $timeout = 10)
    {
        foreach ($build->getServices() as $service) {
            if ($service->getContainer()) {
                try {
                    $this->docker->getContainerManager()->stop($service->getContainer(), $timeout);
                } catch (UnexpectedStatusCodeException $e) {
                    if ($e->getCode() != "304") {
                        throw $e;
                    }
                }

                $this->docker->getContainerManager()->remove($service->getContainer(), true);
            }
        }
    }
}
