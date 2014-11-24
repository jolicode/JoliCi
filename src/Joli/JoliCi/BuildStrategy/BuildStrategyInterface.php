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

use Joli\JoliCi\Job;

/**
 * Interface that all Build strategy must implement
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
interface BuildStrategyInterface
{
    const WORKDIR = "/home/project";

    /**
     * Create / Get jobs for a project
     *
     * @param string $directory Location of project
     *
     * @return \Joli\JoliCi\Job[] Return a list of jobs to create
     */
    public function getJobs($directory);

    /**
     * Prepare a job (generally copy its files to a new directory
     *
     * @param \Joli\JoliCi\Job $job
     *
     * @return void
     */
    public function prepareJob(Job $job);

    /**
     * Return name of the build strategy
     *
     * @return string
     */
    public function getName();

    /**
     * Tell if the build strategy is supported for a project
     *
     * @param string $directory Location of project
     *
     * @return boolean
     */
    public function supportProject($directory);
}
