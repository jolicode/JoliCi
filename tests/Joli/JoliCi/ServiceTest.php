<?php

namespace Joli\JoliCi;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructInitialisesAllTheFields()
    {
        $nameMock       = uniqid();
        $repositoryMock = uniqid();
        $tagMock        = uniqid();
        $configMock     = array('test' => uniqid());

        $serviceMock = new Service($nameMock, $repositoryMock, $tagMock, $configMock);

        $this->assertSame($nameMock, $serviceMock->getName());
        $this->assertSame($repositoryMock, $serviceMock->getRepository());
        $this->assertSame($tagMock, $serviceMock->getTag());
        $this->assertSame($configMock, $serviceMock->getConfig());
    }

    public function testSetAndGetContainer()
    {
        $dockerContainerMock =
            $this->getMockBuilder('Docker\Container')
                 ->setMethods(null)
                 ->getMock();

        $serviceMock =
            $this->getMockBuilder('Joli\JoliCI\Service')
                 ->setMethods(null)
                 ->disableOriginalConstructor()
                 ->getMock();

        $serviceMock->setContainer($dockerContainerMock);

        $this->assertSame($dockerContainerMock, $serviceMock->getContainer());
    }
}
