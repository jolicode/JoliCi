<?php
/*
 * This file is part of JoliCi.
*
* (c) Joel Wurtz <jwurtz@jolicode.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Joli\JoliCi\BuildStrategy;

/**
 * Interface that all Build strategy must implement
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
interface BuildStrategyInterface
{
    const WORKDIR = "/home/project";

    /**
     * Create builds for a project
     *
     * @param string $directory Location of project
     *
     * @return Build[] Return a list of build to create
     */
    public function createBuilds($directory);

    /**
     * Return name of the build
     *
     * @return string
     */
    public function getName();

    /**
     * Tell if the build support a project
     *
     * @param string $directory Location of project
     *
     * @return boolean
     */
    public function supportProject($directory);
}
