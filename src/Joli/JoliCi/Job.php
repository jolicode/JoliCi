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

class Job
{
    const BASE_NAME = 'jolici';

    /**
     * @var string Name of job
     */
    protected $project;

    /**
     * @var string Strategy associated with this job
     */
    protected $strategy;

    /**
     * @var string Uniq key for this "kind" of job
     *
     * This key is not a identifier (the name is the identifier in a job), it's more like a category,
     * job with same parameters MUST have the same uniq key, this key is use to track the history
     * of a job over time (for cleaning, reports, etc ....)
     */
    protected $uniq;

    /**
     * @var array Parameters of this job
     *
     * It mainly depend on the strategy, ie for TravisCi strategy this will include the language used, version used, etc ...
     */
    protected $parameters;

    /**
     * @var string Description of this job (generally a nice name for end user)
     */
    protected $description;

    /**
     * @var \DateTime Date of creation of the job
     */
    protected $created;

    /**
     * @var Service[] Services linked to this job
     */
    private $services = array();

    /**
     * @param string    $project     Project of the job
     * @param string    $strategy    Strategy of the job
     * @param string    $uniq        A uniq identifier for this kind of job
     * @param array     $parameters  Parameters of the job (mainly depend on the strategy)
     * @param string    $description Description of this job (generally a nice name for end user)
     * @param \DateTime $created     Date of creation of the job
     * @param array $services Services linked to the job
     */
    public function __construct($project, $strategy, $uniq, $parameters = array(), $description = "", $created = null, $services = array())
    {
        $this->project     = $project;
        $this->description = $description;
        $this->strategy    = $strategy;
        $this->parameters  = $parameters;
        $this->uniq        = $uniq;

        if (null === $created) {
            $created = new \DateTime();
        }

        $this->created = $created;
        $this->services = $services;
    }

    /**
     * Get name of this job
     *
     * @return string
     */
    public function getName()
    {
        return sprintf('%s:%s', $this->getRepository(), $this->getTag());
    }

    /**
     * Get repository name for docker images job with this strategy
     *
     * @return string
     */
    public function getRepository()
    {
        return sprintf('%s_%s/%s', static::BASE_NAME, strtolower($this->strategy), $this->project);
    }

    /**
     * Generate the tag name for a docker image
     *
     * @return string
     */
    public function getTag()
    {
        return sprintf('%s-%s', $this->uniq, $this->created->format('U'));
    }

    /**
     * Add a service to the job
     *
     * @param Service $service
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * Return all services linked to this job
     *
     * @return Service[]
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Return directory of job
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @return string
     */
    public function getUniq()
    {
        return $this->uniq;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
