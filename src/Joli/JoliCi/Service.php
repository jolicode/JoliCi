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
    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';

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
     * @var string protocol of the service
     */
    private $protocol;

    /**
     * @var integer port number of the service
     */
    private $port;

    /**
     * @var \Docker\Container Container used for this service
     */
    private $container;

    /**
     * @var Exec identifier for the socat proxy
     */
    private $execId;

    public function __construct($name, $repository, $tag, $port, $protocol = Service::PROTOCOL_TCP, $config = array())
    {
        $this->name       = $name;
        $this->repository = $repository;
        $this->tag        = $tag;
        $this->port       = $port;
        $this->protocol   = $protocol;
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

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return Exec
     */
    public function getExecId()
    {
        return $this->execId;
    }

    /**
     * @param Exec $execId
     */
    public function setExecId($execId)
    {
        $this->execId = $execId;
    }
}
