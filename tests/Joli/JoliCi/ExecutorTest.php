<?php

namespace Joli\JoliCi;

use Docker\Container;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger  = $this->getMockBuilder('\Joli\JoliCi\LoggerCallback')->disableOriginalConstructor()->getMock();
        $this->docker  = $this->getMockBuilder('\Docker\Docker')->disableOriginalConstructor()->getMock();
        $this->imageManager = $this->getMockBuilder('\Docker\Manager\ImageManager')->disableOriginalConstructor()->getMock();
        $this->executor = new Executor($this->logger, $this->docker, "");
    }

    public function testBuild()
    {
        $executor = new Executor($this->logger, $this->docker, true, true);

        $this->logger->expects($this->once())
            ->method('getBuildCallback')
            ->will($this->returnValue(function () {}));

        $this->docker->expects($this->once())
            ->method('build')
            ->with($this->isInstanceOf('\Docker\Context\Context'), $this->stringContains('test'), $this->isType('callable'), $this->isTrue(), $this->isTrue());

        $this->docker->expects($this->once())
            ->method('getImageManager')
            ->will($this->returnValue($this->imageManager));

        $this->imageManager->expects($this->once())
            ->method('find');

        $executor->create(new Job("/test", "test", "", ""));
    }

    public function testBuildWithoutCache()
    {
        $executor = new Executor($this->logger, $this->docker, "", false, true);

        $this->logger->expects($this->once())
            ->method('getBuildCallback')
            ->will($this->returnValue(function () {}));

        $this->docker->expects($this->once())
            ->method('build')
            ->with($this->isInstanceOf('\Docker\Context\Context'), $this->stringContains('test'), $this->isType('callable'), $this->isTrue(), $this->isFalse());

        $this->docker->expects($this->once())
            ->method('getImageManager')
            ->will($this->returnValue($this->imageManager));

        $this->imageManager->expects($this->once())
            ->method('find');

        $executor->create(new Job("/test", "test", "", ""));
    }

    public function testBuildNoQuiet()
    {
        $executor = new Executor($this->logger, $this->docker, "", false, false);

        $this->logger->expects($this->once())
            ->method('getBuildCallback')
            ->will($this->returnValue(function () {}));

        $this->docker->expects($this->once())
            ->method('build')
            ->with($this->isInstanceOf('\Docker\Context\Context'), $this->stringContains('test'), $this->isType('callable'), $this->isFalse(), $this->isFalse());

        $this->docker->expects($this->once())
            ->method('getImageManager')
            ->will($this->returnValue($this->imageManager));

        $this->imageManager->expects($this->once())
            ->method('find');

        $executor->create(new Job("/test", "test", "", ""));
    }

    public function testRunTest()
    {
        $containerManager = $this->getMock('\Docker\Container\ContainerManager', array('run', 'attach', 'wait'));
        $container = null;

        $this->docker->expects($this->once())
            ->method('getImageManager')
            ->will($this->returnValue($this->imageManager));

        $this->imageManager->expects($this->once())
            ->method('find');

        $this->docker->expects($this->once())
            ->method('getContainerManager')
            ->will($this->returnValue($containerManager));

        $containerManager->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function (Container $c) use (&$container) {
                $c->setExitCode(10);
                $container = $c;
            }));

        $exitCode = $this->executor->run(new Job("/test", "test", "", ""));

        $this->assertEquals(10, $exitCode);
        $this->assertInstanceOf('\Docker\Container', $container);
    }

    public function testRunTestWithCmdOverride()
    {
        $containerManager = $this->getMock('\Docker\Container\ContainerManager', array('run', 'attach', 'wait'));
        $container = null;

        $this->docker->expects($this->once())
            ->method('getImageManager')
            ->will($this->returnValue($this->imageManager));

        $this->imageManager->expects($this->once())
            ->method('find');

        $this->docker->expects($this->once())
            ->method('getContainerManager')
            ->will($this->returnValue($containerManager));

        $containerManager->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function (Container $c) use(&$container) {
                $c->setExitCode(0);
                $container = $c;
            }));

        $exitCode = $this->executor->run(new Job("/test", "test", "", ""), array("phpunit"));

        $this->assertEquals(0, $exitCode);
        $this->assertInstanceOf('\Docker\Container', $container);
        $this->assertArrayHasKey("Cmd", $container->getConfig());
        $this->assertEquals(array("phpunit"), $container->getConfig()["Cmd"]);
    }

    public function testRunTestWithCmdOverrideAsString()
    {
        $containerManager = $this->getMock('\Docker\Container\ContainerManager', array('run', 'attach', 'wait'));
        $container = null;

        $this->docker->expects($this->once())
            ->method('getImageManager')
            ->will($this->returnValue($this->imageManager));

        $this->imageManager->expects($this->once())
            ->method('find');

        $this->docker->expects($this->once())
            ->method('getContainerManager')
            ->will($this->returnValue($containerManager));

        $containerManager->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function (Container $c) use (&$container) {
                $c->setExitCode(0);
                $container = $c;
            }));

        $exitCode = $this->executor->run(new Job("/test", "test", "", ""), "phpunit");

        $this->assertEquals(0, $exitCode);
        $this->assertInstanceOf('\Docker\Container', $container);
        $this->assertArrayHasKey("Cmd", $container->getConfig());
        $this->assertEquals(array("/bin/bash", "-c", "phpunit"), $container->getConfig()["Cmd"]);
    }
}
