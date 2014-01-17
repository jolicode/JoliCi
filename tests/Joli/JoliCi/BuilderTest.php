<?php

namespace Joli\JoliCi;

use Joli\JoliCi\BuildStrategy\BuildStrategyInterface;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateBuilds()
    {
        $builder = new Builder();
        $builder->pushStrategy(new FooBuildStrategy());

        $builds = $builder->createBuilds("test");

        $this->assertCount(1, $builds);

        $build = $builds[0];

        $this->assertEquals("test", $build->getName());
        $this->assertEquals("/test", $build->getDirectory());
    }

    public function testNoBuildsEmpty()
    {
        $builder = new Builder();
        $builder->pushStrategy(new NoBuildStrategy());

        $builds = $builder->createBuilds("test");

        $this->assertCount(0, $builds);
    }
}

class FooBuildStrategy implements BuildStrategyInterface
{
    public function createBuilds($directory)
    {
        return array(new Build("test", "/test"));
    }

    public function getName()
    {
        return "dummy";
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