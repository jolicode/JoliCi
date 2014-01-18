<?php
/*
 * This file is part of JoliCi.
*
* (c) Joel Wurtz <jwurtz@jolicode.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Joli\JoliCi\BuildStrategy;

use Joli\JoliCi\Build;
use Joli\JoliCi\Filesystem\Filesystem;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * TravisCi implementation for build
 *
 * A project must have a .travis.yml file
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
class TravisCiBuildStrategy implements BuildStrategyInterface
{
    private $mapping = array(
        'php' => array(
            '5.3' => 'Dockerfile.php53',
            '5.4' => 'Dockerfile.php54',
            '5.5' => 'Dockerfile.php55',
        )
    );

    /**
     * @var string Base path for build
     */
    private $buildPath;

    /**
     * @var string Base path for travisci resources
     */
    private $resourcesPath;

    /**
     * @var Filesystem Filesystem service
     */
    private $filesystem;

    public function __construct($buildPath, Filesystem $filesystem = null, $resourcesPath = null)
    {
        $this->buildPath  = $buildPath;
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->resourcesPath = $resourcesPath ?: realpath(__DIR__."/../../../..")."/resources/travisci";
    }

    /*
     * {@inheritdoc}
     */
    public function createBuilds($directory)
    {
        $builds = array();
        $config = Yaml::parse($directory.DIRECTORY_SEPARATOR.".travis.yml");

        $language             = $config['language'];
        $additionalRunContent = $this->parseBeforeScript($config);
        $cmdContent           = $this->parseScript($config);
        $buildRoot            = $this->buildPath.DIRECTORY_SEPARATOR.uniqid();

        if (isset($config[$language])) {
            foreach ($config[$language] as $version) {
                if (isset($this->mapping[$language][(string)$version])) {
                    $dockerfile        = $this->mapping[$language][(string)$version];
                    $dockerFileContent = file_get_contents($this->resourcesPath."/".$dockerfile);
                    $dockerFileContent = sprintf("%s\n%s\n%s", $dockerFileContent, $additionalRunContent, $cmdContent);

                    $buildName = sprintf("%s-%s", $language, $version);
                    $buildDir  = $buildRoot.DIRECTORY_SEPARATOR.$buildName;

                    //Recursive copy of the pull to this directory
                    $this->filesystem->rcopy($directory, $buildDir, true);

                    //Recursive copy of content of the build dir to the root dir
                    file_put_contents($buildDir.DIRECTORY_SEPARATOR."Dockerfile", $dockerFileContent);

                    $builds[] = new Build($buildName, $buildDir);
                }
            }
        }

        return $builds;
    }

    /*
     * {@inheritdoc}
     */
    public function getName()
    {
        return "travisci";
    }

    /*
     * {@inheritdoc}
     */
    public function supportProject($directory)
    {
        return file_exists($directory.DIRECTORY_SEPARATOR.".travis.yml") && is_file($directory.DIRECTORY_SEPARATOR.".travis.yml");
    }

    /**
     * Return content of RUN instructions to be added to Dockerfile given the .travis.yml file
     *
     * @param array $config TravisCi Configuration parsed
     *
     * @return string Content to add to Dockerfile
     */
    private function parseBeforeScript($config)
    {
        $content = "";

        if (!isset($config['before_script'])) {
            return "";
        }

        foreach ($config['before_script'] as $runLine) {
            $content .= "RUN cd /project && ".$runLine."\n";
        }

        return $content;
    }

    /**
     * Return content for CMD instructions base on .travis.yml file
     *
     * @param array $config TravisCi Configuration parsed
     *
     * @return string CMD string to add to Dockerfile
     */
    private function parseScript($config)
    {
        if (!isset($config['script'])) {
            return "CMD ls -l";
        }

        return sprintf("CMD %s", implode(" && ", $config['script']));
    }
}
