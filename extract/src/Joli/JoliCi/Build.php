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

    public function __construct($name, $directory)
    {
        $this->name       = $name;
        $this->directory  = $directory;
        $this->dockername = sprintf("%s-%s", uniqid(), $name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function getDockerName()
    {
        return $this->dockername;
    }
}