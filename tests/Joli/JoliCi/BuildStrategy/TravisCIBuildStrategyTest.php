<?php

namespace Joli\JoliCi\BuildStrategy;

use Joli\JoliCi\Filesystem\Filesystem;
use Joli\JoliCi\Naming;
use org\bovigo\vfs\vfsStream;
use Joli\JoliCi\Builder\DockerfileBuilder;

class TravisCIBuildStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->buildPath = vfsStream::setup('build-path');
        $this->strategy = new TravisCiBuildStrategy(new DockerfileBuilder(), vfsStream::url('build-path'), new Naming(), new Filesystem());
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

    /**
     * @dataProvider createMatrixVersionDataProvider
     */
    public function testCreateMatrixCanObtainVersions($versions)
    {
        $testConfig = [
            'language' => 'php',
            'php'      => $versions,
        ];

        $createMatrix = function ($config) {
            return $this->createMatrix($config);
        };
        $createMatrix = $createMatrix->bindTo($this->strategy, $this->strategy);

        $matrix = $createMatrix($testConfig);

        $this->assertAttributeContains((array) $versions, 'dimensions', $matrix);
    }

    /**
     * @return array
     */
    public function createMatrixVersionDataProvider()
    {
        return [
            // Test with float
            [5.5],
            // Test with string
            ['5.5'],
            // Test with list
            [['5.5', '5.6', '7']]
        ];
    }
}
