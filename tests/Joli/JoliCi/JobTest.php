<?php

namespace Joli\JoliCi;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructInitialisesAllTheFields()
    {
        $projectMock     = uniqid();
        $strategyMock    = uniqid();
        $uniqMock        = uniqid();
        $parametersMock  = array('test' => uniqid());
        $descriptionMock = 'test';
        $createdMock     = new \DateTime();
        $servicesMock    = array('service' => uniqid());

        $jobMock = new Job(
            $projectMock,
            $strategyMock,
            $uniqMock,
            $parametersMock,
            $descriptionMock,
            $createdMock,
            $servicesMock
        );

        $this->assertAttributeSame($projectMock, 'project', $jobMock);
        $this->assertSame($strategyMock, $jobMock->getStrategy());
        $this->assertSame($uniqMock, $jobMock->getUniq());
        $this->assertSame($parametersMock, $jobMock->getParameters());
        $this->assertSame($descriptionMock, $jobMock->getDescription());
        $this->assertSame($createdMock, $jobMock->getCreated());
        $this->assertSame($servicesMock, $jobMock->getServices());
    }

    public function testAddAndGetServices()
    {
        $jobMock =
            $this->getMockBuilder('Joli\JoliCI\Job')
                 ->disableOriginalConstructor()
                 ->setMethods(null)
                 ->getMock();

        $serviceMock = new Service('test', 'test', 'test');

        $this->assertEmpty($jobMock->getServices());

        $jobMock->addService($serviceMock);
        $this->assertEquals(array($serviceMock), $jobMock->getServices());
    }

    public function testToStringReturnsTheNameOfTheJob()
    {
        $jobMock = new Job('project', 'strategy', uniqid());

        $this->assertEquals($jobMock->getName(), $jobMock->__toString());
    }
}
