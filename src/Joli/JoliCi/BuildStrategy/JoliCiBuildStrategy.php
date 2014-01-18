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
     * @param string $buildPath
     */
    public function __construct($buildPath, Filesystem $filesystem = null)
    {
        $this->buildPath  = $buildPath;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /*
     * {@inheritdoc}
     */
    public function createBuilds($directory)
    {
        $builds = array();
        $finder = new Finder();
        $finder->directories();

        $joliCiDir = $directory.DIRECTORY_SEPARATOR.".jolici";

        foreach ($finder->in($joliCiDir) as $dir) {
            $buildName = $dir->getFilename();
            $buildDir  = $this->buildPath.DIRECTORY_SEPARATOR.uniqid().DIRECTORY_SEPARATOR.$buildName;

            //Recursive copy of the pull to this directory
            $this->filesystem->rcopy($directory, $buildDir, true);

            //Recursive copy of content of the build dir to the root dir
            $this->filesystem->rcopy($dir->getRealPath(), $buildDir, true);

            $builds[] = new Build($buildName, $buildDir);
        }

        return $builds;
    }

    /*
     * {@inheritdoc}
     */
    public function getName()
    {
        return "jolici";
    }

    /*
     * {@inheritdoc}
     */
    public function supportProject($directory)
    {
        return file_exists($directory.DIRECTORY_SEPARATOR.".jolici") && is_dir($directory.DIRECTORY_SEPARATOR.".jolici");
    }
}
