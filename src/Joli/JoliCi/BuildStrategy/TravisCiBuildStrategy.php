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
use Joli\JoliCi\Builder\DockerfileBuilder;
use Joli\JoliCi\Filesystem\Filesystem;
use Joli\JoliCi\Matrix;
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
    private $languageVersionKeyMapping = array(
        'ruby' => 'rvm'
    );

    private $defaults = array(
        'php' => array(
            'before_install' => array(),
            'install'        => array('composer install'),
            'before_script'  => array(),
            'script'         => array('phpunit'),
            'env'            => array()
        ),
        'ruby' => array(
            'before_install' => array(),
            'install'        => array('bundle install'),
            'before_script'  => array(),
            'script'         => array('bundle exec rake'),
            'env'            => array()
        ),
        'node_js' => array(
            'before_install' => array(),
            'install'        => array('npm install'),
            'before_script'  => array(),
            'script'         => array('npm test'),
            'env'            => array()
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
     * @param DockerfileBuilder $builder
     * @param string $buildPath
     * @param Filesystem|null $filesystem
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
        $language   = isset($config['language']) ? $config['language'] : 'ruby';
        $versionKey = isset($this->languageVersionKeyMapping[$language]) ? $this->languageVersionKeyMapping[$language] : $language;
        $buildRoot  = $this->buildPath.DIRECTORY_SEPARATOR.uniqid('jolici-');

        $envFromConfig = $this->getConfigValue($config, $language, "env");

        $matrix = new Matrix();
        $matrix->setDimension('environment', $envFromConfig);
        $matrix->setDimension('version', $config[$versionKey]);

        foreach ($matrix->compute() as $possibility) {
            $environment = array();
            $version  = $possibility['version'];
            $envVars  = explode(' ', $possibility['environment'] ?: '');

            foreach ($envVars as $env) {
                if (!empty($env)) {
                    list($key, $value) = explode('=', $env);
                    $environment[$key] = $value;
                }
            }

            $this->builder->setTemplateName(sprintf("%s/Dockerfile-%s.twig", $language, $version));
            $this->builder->setVariables(array(
                'before_install' => $this->getConfigValue($config, $language, 'before_install'),
                'install'        => $this->getConfigValue($config, $language, 'install'),
                'before_script'  => $this->getConfigValue($config, $language, 'before_script'),
                'script'         => $this->getConfigValue($config, $language, 'script'),
                'env'            => $environment
            ));

            $buildName = sprintf("%s-%s", $language, $version);
            $buildDir  = $buildRoot . DIRECTORY_SEPARATOR . $buildName;

            // Recursive copy of the pull to this directory
            $this->filesystem->rcopy($directory, $buildDir, true);

            $this->builder->setOutputName('Dockerfile');

            try {
                $this->builder->writeOnDisk($buildDir);

                $builds[] = new Build($buildName, $buildDir);
            } catch (\Twig_Error_Loader $e) {
                // TODO: template does not exist so language-php is not supported by JoliCI (emit a warning ?)
                $this->filesystem->remove($buildDir);
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
     * Get command lines to add for a configuration value in .travis.yml file
     *
     * @param array  $config   Configuration of travis ci parsed
     * @param string $language Language for getting the default value if no value is set
     * @param string $key      Configuration key
     *
     * @return array A list of command to add to Dockerfile
     */
    private function getConfigValue($config, $language, $key)
    {
        if (!isset($config[$key]) || empty($config[$key])) {
            if (isset($this->defaults[$language][$key])) {
                return $this->defaults[$language][$key];
            }

            return array();
        }

        if (!is_array($config[$key])) {
            return array($config[$key]);
        }

        return $config[$key];
    }
}
