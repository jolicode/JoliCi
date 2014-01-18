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
    private $defaultTestCommand = array(
        'php' => 'phpunit'
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

    /**
     * @param string $buildPath
     */
    public function __construct($buildPath, $resourcesPath, Filesystem $filesystem = null)
    {
        $this->buildPath     = $buildPath;
        $this->filesystem    = $filesystem ?: new Filesystem();
        $this->resourcesPath = $resourcesPath;
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
        $commonContent        = file_get_contents($this->resourcesPath."/Dockerfile");

        if (isset($config[$language]) && file_exists($this->resourcesPath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR."Dockerfile.pre")) {
            $languageContentPre  = file_get_contents($this->resourcesPath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR."Dockerfile.pre");
            $languageContentPost = file_get_contents($this->resourcesPath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR."Dockerfile.post");

            foreach ($config[$language] as $version) {
                if (file_exists($this->resourcesPath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR."Dockerfile")) {
                    $versionContent = file_get_contents($this->resourcesPath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR."Dockerfile");

                    $dockerFileContent = sprintf("%s\n%s\n%s\n%s\n%s\n%s",
                        $commonContent,
                        $languageContentPre,
                        $versionContent,
                        $languageContentPost,
                        $additionalRunContent,
                        $cmdContent
                    );

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
        if (!isset($config['before_script'])) {
            return "";
        }

        if (is_array($config['before_script'])) {
            $config['before_script'] = array_map(function ($value) {
                return sprintf("(%s || echo -n '')", $value);
            }, $config['before_script']);
            return sprintf("RUN cd /project && %s", implode(" && ", $config['before_script']));
        } else {
            return sprintf("RUN cd /project && (%s || exit 0)", $config['before_script']);
        }
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
            return sprintf("CMD %s", $this->defaultTestCommand[$config['language']]);
        }

        if (is_array($config['script'])) {
            return sprintf("CMD %s", implode(" && ", $config['script']));
        } else {
            return sprintf("CMD %s", $config['script']);
        }
    }
}
