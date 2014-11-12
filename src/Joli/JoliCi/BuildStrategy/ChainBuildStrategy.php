<?php

namespace Joli\JoliCi\BuildStrategy;

use Joli\JoliCi\Build;

class ChainBuildStrategy implements BuildStrategyInterface
{
    /**
     * @var BuildStrategyInterface[] A list of strategy to use for this builder
     */
    private $strategies = array();

    /**
     * Add a build strategy to builder
     *
     * @param BuildStrategyInterface $strategy Strategy to add
     */
    public function pushStrategy(BuildStrategyInterface $strategy)
    {
        $this->strategies[$strategy->getName()] = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getBuilds($directory)
    {
        $builds = array();

        foreach ($this->strategies as $strategy) {
            if ($strategy->supportProject($directory)) {
                $builds += $strategy->getBuilds($directory);
            }
        }

        return $builds;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareBuild(Build $build)
    {
        $this->strategies[$build->getStrategy()]->prepareBuild($build);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Chain';
    }

    /**
     * {@inheritdoc}
     */
    public function supportProject($directory)
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supportProject($directory)) {
                return true;
            }
        }

        return false;
    }
}
