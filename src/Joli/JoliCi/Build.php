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

class Build
{
    /**
     * @var string Name of build
     */
    protected $name;

    /**
     * @var string Path of build
     */
    protected $directory;

    /**
     * @var string Name for docker
     */
    protected $dockername;

    /**
     * @param string $directory
     */
    public function __construct($name, $directory)
    {
        $this->name       = $name;
        $this->directory  = $directory;
        $this->dockername = sprintf("%s-%s", uniqid(), $name);
    }

    /**
     * Get name of this build
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return directory of build
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Return the docker name use for image name
     *
     * @return string
     */
    public function getDockerName()
    {
        return $this->dockername;
    }
}