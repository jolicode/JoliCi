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

use Docker\Docker;
use Docker\Context\Context;
use Docker\Exception\ImageNotFoundException;
use Docker\Image;
use Docker\Container as DockerContainer;
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
     * @param Build $build
     * @param array|string $command
     *
     * @return integer
     */
    public function test(Build $build, $command = null)
    {
        $exitCode = 1;

        // 2 Create image for build
        if (false !== $this->create($build)) {
            // 3 Run test if build has created an image
            $exitCode = $this->run($build, $command);
        }

        return $exitCode;
    }

    /**
     * Create a build
     *
     * @param Build $build Build used to create image
     *
     * @return Image|boolean Return the image created if sucessful or false otherwise
     */
    public function create(Build $build)
    {
        $context  = new Context($this->buildPath . DIRECTORY_SEPARATOR . $build->getDirectory());
        $this->docker->build($context, $build->getName(), $this->logger->getBuildCallback(), $this->quietBuild, $this->usecache, true);
        $this->logger->clearStatic();

        try {
            return $this->docker->getImageManager()->find($build->getRepository(), $build->getTag());
        } catch (ImageNotFoundException $e) {
            return false;
        }
    }

    /**
     * Run a build (it's suppose the image exist in docker
     *
     * @param Build $build Build to run
     * @param string|array $command Command to use when run the build (null, by default, will use the command registered to the image)
     *
     * @return integer The exit code of the command run inside (0 = success, otherwise it has failed)
     */
    public function run(Build $build, $command = null)
    {
        $image     = $this->docker->getImageManager()->find($build->getRepository(), $build->getTag());
        $config    = array('HostConfig' => array( 'Links' => array()));

        foreach ($build->getServices() as $service) {
            if ($service->getContainer()) {
                $config['HostConfig']['Links'][] = sprintf('%s:%s', $service->getContainer()->getRuntimeInformations()['Name'], $service->getName());
            }
        }

        $container = new DockerContainer($config);

        if (is_string($command)) {
            $command = array('/bin/bash', '-c', $command);
        }

        if (is_array($command)) {
            $container->setCmd($command);
        }


        $container->setImage($image);

        $this->docker->getContainerManager()->run($container, $this->logger->getRunCallback(), array(), false, $this->timeout);

        return $container->getExitCode();
    }
}
