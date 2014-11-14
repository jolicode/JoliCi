<?php

namespace Joli\JoliCi\BuildStrategy;

use Joli\JoliCi\Build;

class ChainBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetBuilds()
    {
        $builder = new ChainBuildStrategy();
        $builder->pushStrategy(new FooBuildStrategy());

        $builds = $builder->getBuilds("test");

        $this->assertCount(1, $builds);

        $build = $builds[0];

        $this->assertEquals("test", $build->getStrategy());
        $this->assertContains("jolici_test/test:test-", $build->getName());
        $this->assertContains("jolici_test/test:test-", $build->getDirectory());
    }

    public function testNoBuildsEmpty()
    {
        $builder = new ChainBuildStrategy();
        $builder->pushStrategy(new NoBuildStrategy());

        $builds = $builder->getBuilds("test");

        $this->assertCount(0, $builds);
    }
}

class FooBuildStrategy implements BuildStrategyInterface
{
    public function getBuilds($directory)
    {
        return array(new Build("test", "test", "test"));
    }

    public function getName()
    {
        return "dummy";
    }

    public function prepareBuild(Build $build)
    {
    }

    public function supportProject($directory)
    {
        return true;
    }
}

class NoBuildStrategy extends FooBuildStrategy
{
    public function supportProject($directory)
    {
        return false;
    }
}
