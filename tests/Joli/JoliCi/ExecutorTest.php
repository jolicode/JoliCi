<?php

namespace Joli\JoliCi;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger  = $this->getMockBuilder('\Monolog\Logger')->disableOriginalConstructor()->getMock();
        $this->docker  = $this->getMockBuilder('\Docker\Docker')->disableOriginalConstructor()->getMock();

        $this->executor = new Executor($this->logger, $this->docker);
    }

    public function testBuild()
    {
        $executor = new Executor($this->logger, $this->docker, true, true);
        $response = $this->getMock('\Docker\Http\StreamedResponse', array('read'));

        $this->docker->expects($this->once())
            ->method('build')
            ->with($this->isInstanceOf('\Docker\Context\Context'), $this->stringContains('test'), $this->isTrue(), $this->isTrue())
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('read')
            ->with($this->isType('callable'));

        $executor->runBuild("/test", "test");
    }

    public function testBuildWithoutCache()
    {
        $executor = new Executor($this->logger, $this->docker, false, true);
        $response = $this->getMock('\Docker\Http\StreamedResponse', array('read'));

        $this->docker->expects($this->once())
            ->method('build')
            ->with($this->isInstanceOf('\Docker\Context\Context'), $this->stringContains('test'), $this->isTrue(), $this->isFalse())
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('read')
            ->with($this->isType('callable'));

        $executor->runBuild("/test", "test");
    }

    public function testBuildNoQuiet()
    {
        $executor = new Executor($this->logger, $this->docker, false, false);
        $response = $this->getMock('\Docker\Http\StreamedResponse', array('read'));

        $this->docker->expects($this->once())
            ->method('build')
            ->with($this->isInstanceOf('\Docker\Context\Context'), $this->stringContains('test'), $this->isFalse(), $this->isFalse())
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('read')
            ->with($this->isType('callable'));

        $executor->runBuild("/test", "test");
    }

    public function testRunTest()
    {
        $containerManager = $this->getMock('\Docker\Container\ContainerManager', array('run', 'attach', 'wait'));

        $this->docker->expects($this->once())
            ->method('getContainerManager')
            ->will($this->returnValue($containerManager));

        $containerManager->expects($this->once())
            ->method('run')
            ->will($this->returnSelf());

        $containerManager->expects($this->once())
            ->method('attach')
            ->with($this->isInstanceOf('\Docker\Container', $this->isType('callable')))
            ->will($this->returnSelf());

        $containerManager->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());

        $container = $this->executor->runTest("test");

        $this->assertInstanceOf('\Docker\Container', $container);

        $config = $container->getConfig();

        $this->assertArrayHasKey("Image", $config);
        $this->assertEquals("test", $config["Image"]);
    }

public function testRunTestWithCmdOverride()
    {
        $containerManager = $this->getMock('\Docker\Container\ContainerManager', array('run', 'attach', 'wait'));

        $this->docker->expects($this->once())
            ->method('getContainerManager')
            ->will($this->returnValue($containerManager));

        $containerManager->expects($this->any())
            ->method('run')
            ->will($this->returnSelf());
        $containerManager->expects($this->any())
            ->method('attach')
            ->will($this->returnSelf());
        $containerManager->expects($this->any())
            ->method('wait')
            ->will($this->returnSelf());

        $container = $this->executor->runTest("test", array("phpunit"));

        $config = $container->getConfig();

        $this->assertArrayHasKey("Cmd", $config);
        $this->assertEquals(array("phpunit"), $config["Cmd"]);
    }

    public function testRunTestWithCmdOverrideAsString()
    {
        $containerManager = $this->getMock('\Docker\Container\ContainerManager', array('run', 'attach', 'wait'));

        $this->docker->expects($this->once())
            ->method('getContainerManager')
            ->will($this->returnValue($containerManager));

        $containerManager->expects($this->any())
            ->method('run')
            ->will($this->returnSelf());
        $containerManager->expects($this->any())
            ->method('attach')
            ->will($this->returnSelf());
        $containerManager->expects($this->any())
            ->method('wait')
            ->will($this->returnSelf());

        $container = $this->executor->runTest("test", "phpunit");

        $config = $container->getConfig();

        $this->assertArrayHasKey("Cmd", $config);
        $this->assertEquals(array("/bin/bash", "-c", "phpunit"), $config["Cmd"]);
    }
}