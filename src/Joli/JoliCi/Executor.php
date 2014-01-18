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

use Monolog\Logger;

use Docker\Docker;
use Docker\Context\Context;
use Docker\Image;
use Docker\Container;

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

    public function __construct(Logger $logger, Docker $docker, $usecache = true, $quietBuild = true)
    {
        $this->logger = $logger;
        $this->docker = $docker;
        $this->usecache = $usecache;
        $this->quietBuild = $quietBuild;
    }

    /**
     * Run build command
     *
     * @param unknown $directory           Directory where the project to build is
     * @param unknown $dockername          Name of the docker image to create
     */
    public function runBuild($directory, $dockername)
    {
        $logger = $this->logger;

        //Run build
        $context  = new Context($directory);
        $response = $this->docker->build($context, $dockername, $this->quietBuild, $this->usecache);

        if ($this->quietBuild) {
            $response->read();
        } else {
            $response->read(function ($output) use ($logger, $response) {
                $static    = false;
                $staticId  = null;
                $error     = false;
                $needClean = false;

                if ($response->headers->get('Content-Type') == 'application/json') {
                    $output  = json_decode($output, true);
                    $message = "";
                    if (isset($output['stream'])) {
                        $message = $output['stream'];
                    }

                    if (isset($output['status'])) {
                        $message .= " ".$output['status'];
                        $needClean = true;
                    }

                    //Handle "static" messages
                    if (isset($output['id'])) {
                        $static    = true;
                        $staticId  = $output['id'];
                        $needClean = true;
                    }

                    //Only get progress message (but current, total, and start size may be available under progressDetail)
                    if (isset($output['progress'])) {
                        $message .= " ".$output['progress'];
                        $needClean = true;
                    }

                    if (isset($output['err'])) {
                        //#TODO Deal with error
                    }
                } else {
                    $message = $output;
                }

                //remove whitespace and other characters and force a clean message only when needed
                if ($needClean) {
                    $message = trim($message)."\n";
                }

                if (!$error) {
                    $logger->addInfo($message, array('static' => $static, 'static-id' => $staticId));
                } else {
                    $logger->addError($message, array('static' => $static, 'static-id' => $staticId));
                }
            });
        }
    }

    /**
     * Run default command for container
     *
     * @param string $dockername          Name of docker image
     *
     * @return Container return the container executed
     */
    public function runTest($dockername)
    {
        $logger = $this->logger;

        //Execute test
        $image = new Image();
        $image->setRepository($dockername);

        $container = new Container();
        $container->setImage($image);

        $this->docker->getContainerManager()->run($container)->attach($container, function ($type, $content) use ($logger) {
            if ($type === 2) {
                $logger->addError($content);
            } else {
                $logger->addInfo($content);
            }
        })->wait($container);

        return $container;
    }
}