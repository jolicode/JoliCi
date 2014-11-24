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
use Joli\JoliCi\Filesystem\Filesystem;
use Joli\JoliCi\Naming;
use Symfony\Component\Finder\Finder;

/**
 * JoliCi implementation for build
 *
 * A project must have a .jolici directory, each directory inside this one will be a type of build and must contain a Dockerfile to be executable
 *
 * @author Joel Wurtz <jwurtz@jolicode.com>
 */
class JoliCiBuildStrategy implements BuildStrategyInterface
{
    /**
     * @var string Base path for build
     */
    private $buildPath;

    /**
     * @var Filesystem Filesystem service
     */
    private $filesystem;

    /**
     * @var Naming Use to name the image created
     */
    private $naming;

    /**
     * @param string     $buildPath  Directory where build must be created
     * @param Naming     $naming     Naming service
     * @param Filesystem $filesystem Filesystem service
     */
    public function __construct($buildPath, Naming $naming, Filesystem $filesystem)
    {
        $this->buildPath  = $buildPath;
        $this->naming     = $naming;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobs($directory)
    {
        $builds = array();
        $finder = new Finder();
        $finder->directories();

        foreach ($finder->in($this->getJoliCiStrategyDirectory($directory)) as $dir) {
            $builds[] = new Job(
                $this->naming->getProjectName($directory),
                $this->getName(),
                $this->naming->getUniqueKey(array('build' => $dir->getFilename())),
                array(
                    'origin' => $directory,
                    'build'  => $dir->getRealPath(),
                ),
                "JoliCi Build: ".$dir->getFilename()
            );
        }

        return $builds;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareJob(Job $job)
    {
        $origin = $job->getParameters()['origin'];
        $target = $this->buildPath.DIRECTORY_SEPARATOR. $job->getDirectory();
        $build  = $job->getParameters()['build'];

        // First mirroring target
        $this->filesystem->mirror($origin, $target, null, array(
            'delete'   => true,
            'override' => true,
        ));

        // Second override target with build dir
        $this->filesystem->mirror($build, $target, null, array(
            'delete'   => false,
            'override' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "JoliCi";
    }

    /**
     * {@inheritdoc}
     */
    public function supportProject($directory)
    {
        return file_exists($this->getJoliCiStrategyDirectory($directory)) && is_dir($this->getJoliCiStrategyDirectory($directory));
    }

    /**
     * Return the jolici strategy directory where there must be builds
     *
     * @param  string $projectPath
     * @return string
     */
    protected function getJoliCiStrategyDirectory($projectPath)
    {
        return $projectPath.DIRECTORY_SEPARATOR.'.jolici';
    }
}
