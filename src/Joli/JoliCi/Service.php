<?php

namespace Joli\JoliCi;

use Docker\Container as DockerContainer;

/**
 * A service is just an application or a tool link to a build which helps running tests
 *
 * It can be for example a MySQL database which contains the needed fixtures in order
 * to make functional tests
 *
 * Multiple services can be link to a Job and they are started before creation of the Job.
 * Once the Job is finished, all services linkes are shutdown and reset to initial state for subsequent Job
 */
class Service
{
    /**
     * @var string Service name (use in link to container)
     */
    private $name;

    /**
     * @var string Repository for this service (from docker hub)
     */
    private $repository;

    /**
     * @var string Tag for this service (generally the version)
     */
    private $tag;

    /**
     * @var array Config when creating a container
     */
    private $config;

    /**
     * @var \Docker\Container Container used for this service
     */
    private $container;

    public function __construct($name, $repository, $tag, $config = array())
    {
        $this->name       = $name;
        $this->repository = $repository;
        $this->tag        = $tag;
        $this->config     = $config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return DockerContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param DockerContainer $container
     */
    public function setContainer(DockerContainer $container)
    {
        $this->container = $container;
    }
}
