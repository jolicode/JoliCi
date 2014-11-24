<?php

namespace Joli\JoliCi\BuildStrategy;

use Joli\JoliCi\Job;

class ChainBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetJobs()
    {
        $builder = new ChainBuildStrategy();
        $builder->pushStrategy(new FooBuildStrategy());

        $jobs = $builder->getJobs("test");

        $this->assertCount(1, $jobs);

        $job = $jobs[0];

        $this->assertEquals("test", $job->getStrategy());
        $this->assertContains("jolici_test/test:test-", $job->getName());
        $this->assertContains("jolici_test/test:test-", $job->getDirectory());
    }

    public function testNoJobsEmpty()
    {
        $builder = new ChainBuildStrategy();
        $builder->pushStrategy(new NoBuildStrategy());

        $jobs = $builder->getJobs("test");

        $this->assertCount(0, $jobs);
    }
}

class FooBuildStrategy implements BuildStrategyInterface
{
    public function getJobs($directory)
    {
        return array(new Job("test", "test", "test"));
    }

    public function getName()
    {
        return "dummy";
    }

    public function prepareJob(Job $job)
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
