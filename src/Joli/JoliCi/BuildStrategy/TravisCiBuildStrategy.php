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
use Joli\JoliCi\Builder\DockerfileBuilder;

/**
 * TravisCi implementation for build
 *
 * A project must have a .travis.yml file
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
class TravisCiBuildStrategy implements BuildStrategyInterface
{
    private $languageVersionKeyMapping = array(
        'php' => 'php',
        'ruby' => 'rvm'
    );

    private $defaults = array(
        'php' => array(
            'before_install' => array(),
            'install'        => array('composer install'),
            'before_script'  => array(),
            'script'         => array('phpunit'),
        ),
        'ruby' => array(
            'before_install' => array(),
            'install'        => array('bundle install'),
            'before_script'  => array(),
            'script'         => array('bundle exec rake'),
        ),
        'node_js' => array(
            'before_install' => array(),
            'install'        => array('npm install'),
            'before_script'  => array(),
            'script'         => array('npm test'),
        ),
    );

    /**
     * @var DockerfileBuilder Builder for dockerfile
     */
    private $builder;

    /**
     * @var string Build path for project
     */
    private $buildPath;

    /**
     * @var Filesystem Filesystem service
     */
    private $filesystem;

    /**
     * @param string $buildPath
     */
    public function __construct(DockerfileBuilder $builder, $buildPath, Filesystem $filesystem = null)
    {
        $this->builder    = $builder;
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->buildPath  = $buildPath;
    }

    /*
     * {@inheritdoc}
     */
    public function createBuilds($directory)
    {
        $builds     = array();
        $config     = Yaml::parse($directory.DIRECTORY_SEPARATOR.".travis.yml");
        $language   = $config['language'];
        $versionKey = isset($this->languageVersionKeyMapping[$language]) ? $this->languageVersionKeyMapping[$language] : $language;
        $buildRoot  = $this->buildPath.DIRECTORY_SEPARATOR.uniqid();

        foreach ($config[$versionKey] as $version) {
            $this->builder->setTemplateName(sprintf("%s/Dockerfile-%s.twig", $language, $version));
            $this->builder->setVariables(array(
                'before_install' => $this->getAsArray($config, 'before_install'),
                'install'        => $this->getAsArray($config, 'install'),
                'before_script'  => $this->getAsArray($config, 'before_script'),
                'script'         => $this->getAsArray($config, 'script')
            ));

            $buildName = sprintf("%s-%s", $language, $version);
            $buildDir  = $buildRoot.DIRECTORY_SEPARATOR.$buildName;

            //Recursive copy of the pull to this directory
            $this->filesystem->rcopy($directory, $buildDir, true);

            $this->builder->setOutputName('Dockerfile');
            $this->builder->writeOnDisk($buildDir);

            $builds[] = new Build($buildName, $buildDir);
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

    private function getAsArray($config, $key)
    {
        if (!isset($config[$key])) {
            return array();
        }

        if (!is_array($config[$key])) {
            return array($config[$key]);
        }

        return $config[$key];
    }
}
