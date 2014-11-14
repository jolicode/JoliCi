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

    public function testCreateBuilds()
    {
        $builds  = $this->strategy->getBuilds(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project1");

        $this->assertCount(1, $builds);
    }

    public function testPrepareBuild()
    {
        $builds = $this->strategy->getBuilds(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."jolici".DIRECTORY_SEPARATOR."project1");
        $build = $builds[0];

        $this->strategy->prepareBuild($build);

        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $build->getDirectory())));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $build->getDirectory())."/Dockerfile"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $build->getDirectory())."/foo"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $build->getDirectory())."/.jolici"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $build->getDirectory())."/.jolici/test"));
        $this->assertTrue($this->buildPath->hasChild(vfsStream::path(vfsStream::url('build-path') . DIRECTORY_SEPARATOR . $build->getDirectory())."/.jolici/test/Dockerfile"));
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
