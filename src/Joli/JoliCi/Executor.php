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
     * @var Logger
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

    public function __construct(Logger $logger, Docker $docker, $usecache = true, $quietBuild = true, $timeout = 600)
    {
        $this->logger = $logger;
        $this->docker = $docker;
        $this->usecache = $usecache;
        $this->quietBuild = $quietBuild;
        $this->timeout = $timeout;
    }

    /**
     * Run build command
     *
     * @param string $directory           Directory where the project to build is
     * @param string $dockername          Name of the docker image to create
     *
     * @return boolean Return true on build success, false otherwise
     */
    public function runBuild($directory, $dockername)
    {
        $logger = $this->logger;

        // Run build
        $context  = new Context($directory);
        $response = $this->docker->build($context, $dockername, $this->quietBuild, $this->usecache, false, false);
        $error    = false;

        $response->read(function ($output) use ($logger, $response, &$error) {
            $static    = false;
            $staticId  = null;

            if ($response->headers->get('Content-Type') == 'application/json') {
                $output  = json_decode($output, true);
                $message = "";
                if (isset($output['stream'])) {
                    $message = $output['stream'];
                }

                if (isset($output['status'])) {
                    $message .= " ".$output['status'];
                }

                //Handle "static" messages
                if (isset($output['id'])) {
                    $static    = true;
                    $staticId  = $output['id'];
                }

                // Only get progress message (but current, total, and start size may be available under progressDetail)
                if (isset($output['progress'])) {
                    $message .= " ".$output['progress'];
                }

                if (isset($output['error'])) {
                    $error = true;
                    $message = $output['error'];
                }
            } else {
                $message = $output;
            }

            if (!$error) {
                $logger->addDebug($message, array('static' => $static, 'static-id' => $staticId));
            } else {
                $logger->addError($message, array('static' => $static, 'static-id' => $staticId));
            }
        });

        $logger->addDebug("", array('clear-static' => true));

        return !$error;
    }

    /**
     * Run default command for DockerContainer
     *
     * @param string        $dockername  Name of docker image
     * @param string|array  $cmdOverride Override default command with this one
     *
     * @return DockerContainer return the container executed
     */
    public function runTest($dockername, $cmdOverride = array())
    {
        $logger = $this->logger;

        // Execute test
        $config = array();
        $image = new Image();
        $image->setRepository($dockername);

        if (is_string($cmdOverride)) {
            $cmdOverride = array('/bin/bash', '-c', $cmdOverride);
        }

        $config['Cmd'] = $cmdOverride;

        $container = new DockerContainer($config);
        $container->setImage($image);

        // Find better way to pass timeout
        $currentTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $this->timeout);

        $this->docker->getContainerManager()->run($container, function ($content, $type) use ($logger) {
            if ($type === 2) {
                $logger->addError($content);
            } else {
                $logger->addInfo($content);
            }
        });

        ini_set('default_socket_timeout', $currentTimeout);

        return $container;
    }
}