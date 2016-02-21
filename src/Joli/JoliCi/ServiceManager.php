<?php

namespace Joli\JoliCi;

use Docker\API\Model\ContainerConfig;
use Docker\Container as DockerContainer;
use Docker\Docker;
use Docker\Exception\ImageNotFoundException;
use Docker\Exception\UnexpectedStatusCodeException;
use Docker\Manager\ImageManager;
use Http\Client\Plugin\Exception\ClientErrorException;
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
                $this->docker->getImageManager()->find(sprintf('%s:%s', $service->getRepository(), $service->getTag()));
            } catch (ClientErrorException $e) {
                if ($e->getResponse()->getStatusCode() == 404) {
                    $buildStream = $this->docker->getImageManager()->create(null, [
                        'fromImage' => sprintf('%s:%s', $service->getRepository(), $service->getTag())
                    ], ImageManager::FETCH_STREAM);

                    $buildStream->onFrame($this->logger->getBuildCallback());
                    $buildStream->wait();
                } else {
                    throw $e;
                }
            }

            $serviceConfig = $service->getConfig();
            $containerConfig = new ContainerConfig();
            $containerConfig->setImage(sprintf('%s:%s', $service->getRepository(), $service->getTag()));
            $containerConfig->setLabels([
                'com.jolici.container=true'
            ]);

            if (isset($serviceConfig['Env'])) {
                $containerConfig->setEnv($serviceConfig['Env']);
            }

            $containerCreateResult = $this->docker->getContainerManager()->create($containerConfig);
            $this->docker->getContainerManager()->start($containerCreateResult->getId());
            $service->setContainer($containerCreateResult->getId());
        }
    }

    /**
     * Stop services for a Job and reinit volumes
     *
     * @param Job $job     The job to stop services
     * @param int $timeout Timeout to wait before killing the service
     */
    public function stop(Job $job, $timeout = 10)
    {
        foreach ($job->getServices() as $service) {
            if ($service->getContainer()) {
                try {
                    $this->docker->getContainerManager()->stop($service->getContainer(), [
                        't' => $timeout
                    ]);
                } catch (ClientErrorException $e) {
                    if ($e->getResponse()->getStatusCode() != 304) {
                        throw $e;
                    }
                }

                $this->docker->getContainerManager()->remove($service->getContainer(), [
                    'v' => true,
                    'force' => true
                ]);

                $service->setContainer(null);
            }
        }
    }
}
