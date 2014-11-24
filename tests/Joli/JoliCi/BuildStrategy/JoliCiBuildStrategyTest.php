<?php

namespace Joli\JoliCi;

use Joli\JoliCi\BuildStrategy\JoliCiBuildStrategy;
use Joli\JoliCi\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;

class JoliCiBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->buildPath = vfsStream::setup('build-path');
        $this->strategy = new JoliCiBuildStrategy(vfsStream::url('build-path'), new Naming(), new Filesystem());
    }

    public function testCreateJobs()
    {
        $jobs  = $this->strategy->getJobs(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project1");

        $this->assertCount(1, $jobs);
    }

    public function testPrepareJob()
    {
        $jobs = $this->strategy->getJobs(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project1");
        $job  = $jobs[0];

        $this->strategy->prepareJob($job);

        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $job->getDirectory())));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $job->getDirectory())."/Dockerfile"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $job->getDirectory())."/foo"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $job->getDirectory())."/.jolici"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $job->getDirectory())."/.jolici/test"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $job->getDirectory())."/.jolici/test/Dockerfile"));
    }

    public function testSupportTrue()
    {
        $support = $this->strategy->supportProject(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project1");

        $this->assertTrue($support);
    }

    public function testSupportFalse()
    {
        $support = $this->strategy->supportProject(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project2");

        $this->assertFalse($support);
    }
}
