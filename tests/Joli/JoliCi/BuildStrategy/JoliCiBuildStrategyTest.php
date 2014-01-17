<?php

namespace Joli\JoliCi;

use Joli\JoliCi\BuildStrategy\JoliCiBuildStrategy;
use org\bovigo\vfs\vfsStream;

class JoliCiBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->buildPath = vfsStream::setup('build-path');
        $this->strategy = new JoliCiBuildStrategy(vfsStream::url('build-path'));
    }

    public function testCreateBuilds()
    {
        $builds  = $this->strategy->createBuilds(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project1");

        $this->assertCount(1, $builds);

        $build = $builds[0];

        $this->assertTrue($this->buildPath->hasChild(vfsStream::path($build->getDirectory())));
        $this->assertContains("test", $build->getDockerName());

        $this->assertTrue($this->buildPath->hasChild(vfsStream::path($build->getDirectory())."/Dockerfile"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path($build->getDirectory())."/foo"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path($build->getDirectory())."/.jolici"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path($build->getDirectory())."/.jolici/test"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path($build->getDirectory())."/.jolici/test/Dockerfile"));
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