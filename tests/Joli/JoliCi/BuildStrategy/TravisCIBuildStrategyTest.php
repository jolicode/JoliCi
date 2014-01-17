<?php

namespace Joli\JoliCi;

use org\bovigo\vfs\vfsStream;
use Joli\JoliCi\BuildStrategy\TravisCiBuildStrategy;

class TravisCiBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->buildPath = vfsStream::setup('build-path');
        $this->strategy = new TravisCiBuildStrategy(vfsStream::url('build-path'));
    }

    public function testSupportTrue()
    {
        $support = $this->strategy->supportProject(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."travisci".DIRECTORY_SEPARATOR."project1");

        $this->assertTrue($support);
    }

    public function testSupportFalse()
    {
        $support = $this->strategy->supportProject(__DIR__.DIRECTORY_SEPARATOR."fixtures".DIRECTORY_SEPARATOR."travisci".DIRECTORY_SEPARATOR."project2");

        $this->assertFalse($support);
    }
}