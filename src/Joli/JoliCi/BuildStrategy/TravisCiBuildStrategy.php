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

use Joli\JoliCi\Job;
use Joli\JoliCi\Builder\DockerfileBuilder;
use Joli\JoliCi\Filesystem\Filesystem;
use Joli\JoliCi\Matrix;
use Joli\JoliCi\Naming;
use Joli\JoliCi\Service;
use Symfony\Component\Yaml\Yaml;

/**
 * TravisCi implementation for build strategy
 *
 * A project must have a .travis.yml file
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
class TravisCiBuildStrategy implements BuildStrategyInterface
{
    private $languageVersionKeyMapping = array(
        'ruby' => 'rvm',
    );

    private $defaults = array(
        'php' => array(
            'before_install' => array(),
            'install'        => array(),
            'before_script'  => array(),
            'script'         => array('phpunit'),
            'env'            => array(),
        ),
        'ruby' => array(
            'before_install' => array(),
            'install'        => array(),
            'before_script'  => array(),
            'script'         => array('bundle exec rake'),
            'env'            => array(),
        ),
        'node_js' => array(
            'before_install' => array(),
            'install'        => array(),
            'before_script'  => array(),
            'script'         => array('npm test'),
            'env'            => array(),
        ),
    );

    private $servicesMapping = array(
        'mongodb' => array(
            'repository' => 'mongo',
            'tag' => '2.6',
            'port' => 27017,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'mysql'   => array(
            'repository' => 'mysql',
            'tag' => '5.5',
            'port' => 3306,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array(
                'Env' => array(
                    'MYSQL_ROOT_PASSWORD=""',
                    'MYSQL_USER=travis',
                    'MYSQL_PASSWORD=""'
                )
            )
        ),
        'postgresql' => array(
            'repository' => 'postgres',
            'tag' => '9.1',
            'port' => 5432,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'couchdb' => array(
            'repository' => 'fedora/couchdb',
            'tag' => 'latest',
            'port' => 5984,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'rabbitmq' => array(
            'repository' => 'rabbitmq',
            'tag' => 'latest',
            'port' => 5672,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'memcached' => array(
            'repository' => 'memcached',
            'tag' => 'latest',
            'port' => 11211,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'redis-server' => array(
            'repository' => 'redis',
            'tag' => '2.8',
            'port' => 6379,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'cassandra' => array(
            'repository' => 'spotify/cassandra',
            'tag' => 'latest',
            'port' => 9042,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'neo4j' => array(
            'repository' => 'tpires/neo4j',
            'tag' => 'latest',
            'port' => 7474,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
        ),
        'elasticsearch' => array(
            'repository' => 'dockerfile/elasticsearch',
            'tag' => 'latest',
            'port' => 9200,
            'protocol' => Service::PROTOCOL_TCP,
            'config' => array()
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
     * @var \Joli\JoliCi\Naming Naming service to create docker name for images
     */
    private $naming;

    /**
     * @param DockerfileBuilder $builder    Twig Builder for Dockerfile
     * @param string            $buildPath  Directory where builds are created
     * @param Naming            $naming     Naming service
     * @param Filesystem        $filesystem Filesystem service
     */
    public function __construct(DockerfileBuilder $builder, $buildPath, Naming $naming, Filesystem $filesystem)
    {
        $this->builder    = $builder;
        $this->buildPath  = $buildPath;
        $this->naming     = $naming;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobs($directory)
    {
        $jobs       = array();
        $config     = Yaml::parse(file_get_contents($directory.DIRECTORY_SEPARATOR.".travis.yml"));
        $matrix     = $this->createMatrix($config);
        $services   = $this->getServices($config);
        $timezone   = ini_get('date.timezone');

        foreach ($matrix->compute() as $possibility) {
            $parameters   = array(
                'language' => $possibility['language'],
                'version' => $possibility['version'],
                'environment' => $possibility['environment'],
            );

            $description = sprintf('%s = %s', $possibility['language'], $possibility['version']);

            if ($possibility['environment'] !== null) {
                $description .= sprintf(', Environment: %s', json_encode($possibility['environment']));
            }

            $jobs[] = new Job($this->naming->getProjectName($directory), $this->getName(), $this->naming->getUniqueKey($parameters), array(
                'language'       => $possibility['language'],
                'version'        => $possibility['version'],
                'before_install' => $possibility['before_install'],
                'install'        => $possibility['install'],
                'before_script'  => $possibility['before_script'],
                'script'         => $possibility['script'],
                'env'            => $possibility['environment'],
                'global_env'     => $possibility['global_env'],
                'timezone'       => $timezone,
                'origin'         => realpath($directory),
            ), $description, null, $services);
        }

        return $jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareJob(Job $job)
    {
        $parameters = $job->getParameters();
        $origin     = $parameters['origin'];
        $target     = $this->buildPath.DIRECTORY_SEPARATOR. $job->getDirectory();

        // First mirroring target
        $this->filesystem->mirror($origin, $target, null, array(
            'delete' => true,
            'override' => true,
        ));

        // Create dockerfile
        $this->builder->setTemplateName(sprintf("%s/Dockerfile-%s.twig", $parameters['language'], $parameters['version']));
        $this->builder->setVariables($parameters);
        $this->builder->setOutputName('Dockerfile');
        $this->builder->writeOnDisk($target);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "TravisCi";
    }

    /**
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

    /**
     * Create matrix of build
     *
     * @param array $config
     *
     * @return Matrix
     */
    protected function createMatrix($config)
    {
        $language         = isset($config['language']) ? $config['language'] : 'ruby';
        $versionKey       = isset($this->languageVersionKeyMapping[$language]) ? $this->languageVersionKeyMapping[$language] : $language;
        $environmentLines = $this->getConfigValue($config, $language, "env");
        $environnements   = array();
        $globalEnv        = array();
        $matrixEnv        = $environmentLines;
        $versions         = $config[$versionKey];

        foreach ($versions as $key => $version) {
            if (!$this->isLanguageVersionSupported($language, $version)) {
                unset($versions[$key]);
            }
        }

        if (isset($environmentLines['matrix'])) {
            $matrixEnv = $environmentLines['matrix'];
        }

        if (isset($environmentLines['global'])) {
            foreach ($environmentLines['global'] as $environementVariable) {
                list ($key, $value) = $this->parseEnvironementVariable($environementVariable);
                $globalEnv = array_merge($globalEnv, array($key => $value));
            }

            if (!isset($environmentLines['matrix'])) {
                $matrixEnv = array();
            }
        }

        // Parsing environnements
        foreach ($matrixEnv as $environmentLine) {
            $environnements[] = $this->parseEnvironmentLine($environmentLine);
        }

        $matrix = new Matrix();
        $matrix->setDimension('language', array($language));
        $matrix->setDimension('environment', $environnements);
        $matrix->setDimension('global_env', array($globalEnv));
        $matrix->setDimension('version', $versions);
        $matrix->setDimension('before_install', array($this->getConfigValue($config, $language, 'before_install')));
        $matrix->setDimension('install', array($this->getConfigValue($config, $language, 'install')));
        $matrix->setDimension('before_script', array($this->getConfigValue($config, $language, 'before_script')));
        $matrix->setDimension('script', array($this->getConfigValue($config, $language, 'script')));


        return $matrix;
    }

    /**
     * Get services list from travis ci configuration file
     *
     * @param $config
     *
     * @return Service[]
     */
    protected function getServices($config)
    {
        $services       = array();
        $travisServices = isset($config['services']) && is_array($config['services']) ? $config['services'] : array();

        foreach ($travisServices as $service) {
            if (isset($this->servicesMapping[$service])) {
                $services[] = new Service(
                    $service,
                    $this->servicesMapping[$service]['repository'],
                    $this->servicesMapping[$service]['tag'],
                    $this->servicesMapping[$service]['port'],
                    $this->servicesMapping[$service]['protocol'],
                    $this->servicesMapping[$service]['config']
                );
            }
        }

        return $services;
    }

    /**
     * Parse an environnement line from Travis to return an array of variables
     *
     * Transform:
     *   "A=B C=D"
     * Into:
     *   array('a' => 'b', 'c' => 'd')
     *
     * @param $environmentLine
     * @return array
     */
    private function parseEnvironmentLine($environmentLine)
    {
        $variables     = array();@
        $variableLines = explode(' ', $environmentLine ?: '');

        foreach ($variableLines as $variableLine) {
            if (!empty($variableLine)) {
                list($key, $value) = $this->parseEnvironementVariable($variableLine);
                $variables[$key]   = $value;
            }
        }

        return $variables;
    }

    /**
     * Parse an envar
     *
     * @param $envVar
     * @return array<Key, Value>
     */
    private function parseEnvironementVariable($envVar)
    {
        return explode('=', $envVar);
    }

    private function isLanguageVersionSupported($language, $version)
    {
        return file_exists(__DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'templates'
            . DIRECTORY_SEPARATOR . $language
            . DIRECTORY_SEPARATOR . 'Dockerfile-' . $version . '.twig');
    }
}
