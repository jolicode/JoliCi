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

use Joli\JoliCi\BuildStrategy\BuildStrategyInterface;

/**
 * Create builds from commit
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
class Builder
{
    /**
     * @var BuildStrategyInterface[] A list of strategy to use for this builder
     */
    private $strategies = array();

    /**
     * Create builds from directory
     *
     * @param string $directory Commit where we build commit from
     *
     * @return Build[]
     */
    public function createBuilds($directory)
    {
        $builds = array();

        foreach ($this->strategies as $strategy) {
            //For each strategies working with this project
            if ($strategy->supportProject($directory)) {
                //We get builds
                $newBuilds = $strategy->createBuilds($directory);
                $builds    = array_merge($builds, $newBuilds);
            }
        }

        return $builds;
    }

    /**
     * Add a build strategy to builder
     *
     * @param BuildStrategyInterface $strategy Strategy to add
     */
    public function pushStrategy(BuildStrategyInterface $strategy)
    {
        $this->strategies[] = $strategy;
    }
}
