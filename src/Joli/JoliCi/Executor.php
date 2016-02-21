<?php
/*
 * This file is part of JoliCi.
*
* (c) Joel Wurtz <jwurtz@jolicode.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Joli\JoliCi;

use Docker\API\Model\BuildInfo;
use Docker\API\Model\ContainerConfig;
use Docker\API\Model\HostConfig;
use Docker\Docker;
use Docker\Context\Context;
use Docker\Manager\ContainerManager;
use Docker\Manager\ImageManager;
use Docker\Stream\BuildStream;
use Http\Client\Plugin\Exception\ClientErrorException;
use Monolog\Logger;

class Executor
{
    /**
     * Docker client
     *
     * @var Docker
     */
    protected $docker;

    /**
     * Logger to log message when building
     *
     * @var LoggerCallback
     */
    protected $logger;

    /**
     * @var boolean Use cache when building
     */
    private $usecache = true;

    /**
     * @var boolean Use cache when building
     */
    private $quietBuild = true;

    /**
     * @var integer Default timeout for run
     */
    private $timeout = 600;

    /**
     * @var string Base directory where builds are located
     */
    private $buildPath;

    public function __construct(LoggerCallback $logger, Docker $docker, $buildPath, $usecache = true, $quietBuild = true, $timeout = 600)
    {
        $this->logger     = $logger;
        $this->docker     = $docker;
        $this->usecache   = $usecache;
        $this->quietBuild = $quietBuild;
        $this->timeout    = $timeout;
        $this->buildPath  = $buildPath;
    }

    /**
     * Test a build
     *
     * @param Job $build
     * @param array|string $command
     *
     * @return integer
     */
    public function test(Job $build, $command = null)
    {
        $exitCode = 1;

        if (false !== $this->create($build)) {
            $exitCode = $this->run($build, $command);
        }

        return $exitCode;
    }

    /**
     * Create a build
     *
     * @param Job $job Build used to create image
     *
     * @return \Docker\API\Model\Image|boolean Return the image created if successful or false otherwise
     */
    public function create(Job $job)
    {
        $context  = new Context($this->buildPath . DIRECTORY_SEPARATOR . $job->getDirectory());

        $buildStream = $this->docker->getImageManager()->build($context->toStream(), [
            't' => $job->getName(),
            'q' => $this->quietBuild,
            'nocache' => !$this->usecache
        ], ImageManager::FETCH_STREAM);

        $buildStream->onFrame($this->logger->getBuildCallback());
        $buildStream->wait();

        try {
            return $this->docker->getImageManager()->find($job->getName());
        } catch (ClientErrorException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Run a build (it's suppose the image exist in docker
     *
     * @param Job $job Build to run
     * @param string|array $command Command to use when run the build (null, by default, will use the command registered to the image)
     *
     * @return integer The exit code of the command run inside (0 = success, otherwise it has failed)
     */
    public function run(Job $job, $command)
    {
        if (is_string($command)) {
            $command = ['/bin/bash', '-c', $command];
        }

        $image = $this->docker->getImageManager()->find($job->getName());

        $hostConfig = new HostConfig();

        $config = new ContainerConfig();
        $config->setCmd($command);
        $config->setImage($image->getId());
        $config->setHostConfig($hostConfig);
        $config->setLabels(new \ArrayObject([
            'com.jolici.container=true'
        ]));
        $config->setAttachStderr(true);
        $config->setAttachStdout(true);

        $links = [];

        foreach ($job->getServices() as $service) {
            if ($service->getContainer()) {
                $serviceContainer = $this->docker->getContainerManager()->find($service->getContainer());

                $links[] = sprintf('%s:%s', $serviceContainer->getName(), $service->getName());
            }
        }

        $hostConfig->setLinks($links);

        $containerCreateResult = $this->docker->getContainerManager()->create($config);
        $attachStream = $this->docker->getContainerManager()->attach($containerCreateResult->getId(), [
            'stream' => true,
            'stdout' => true,
            'stderr' => true,
        ], ContainerManager::FETCH_STREAM);

        $attachStream->onStdout($this->logger->getRunStdoutCallback());
        $attachStream->onStderr($this->logger->getRunStderrCallback());

        $this->docker->getContainerManager()->start($containerCreateResult->getId());

        $attachStream->wait();

        $containerWait = $this->docker->getContainerManager()->wait($containerCreateResult->getId());

        return $containerWait->getStatusCode();
    }
}
