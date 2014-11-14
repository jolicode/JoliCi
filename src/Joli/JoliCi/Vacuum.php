<?php

namespace Joli\JoliCi;

use Docker\Docker;
use Docker\Image;
use Joli\JoliCi\BuildStrategy\BuildStrategyInterface;
use Joli\JoliCi\Filesystem\Filesystem;

class Vacuum
{
    /**
     * @var \Docker\Docker Docker API Client
     */
    private $docker;

    /**
     * @var Naming Service use to create name for image and project
     */
    private $naming;

    /**
     * @var BuildStrategy\BuildStrategyInterface Strategy where we want to clean the builds
     */
    private $strategy;

    /**
     * @var string Location where the build are
     */
    private $buildPath;

    /**
     * @var \Joli\JoliCi\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @param Docker                 $docker     Docker API Client
     * @param Naming                 $naming     Naming service
     * @param BuildStrategyInterface $strategy   Strategy used to create builds
     * @param Filesystem             $filesystem Filesystem service
     * @param string                 $buildPath  Directory where builds are created
     */
    public function __construct(Docker $docker, Naming $naming, BuildStrategyInterface $strategy, Filesystem $filesystem, $buildPath)
    {
        $this->docker     = $docker;
        $this->naming     = $naming;
        $this->strategy   = $strategy;
        $this->buildPath  = $buildPath;
        $this->filesystem = $filesystem;
    }

    /**
     * Clean containers, images and directory from a project
     *
     * @param string  $projectPath Location of the project
     * @param int     $keep        How many versions does we need to keep (1 is the default in order to have cache for each test)
     * @param boolean $force       Force removal for images
     */
    public function clean($projectPath, $keep = 1, $force = false)
    {
        $builds = $this->getBuildsToRemove($projectPath, $keep);

        $this->cleanDirectories($builds);
        $this->cleanContainers($builds);
        $this->cleanImages($builds, $force);
    }

    /**
     * Clean directories for given builds
     *
     * @param \Joli\JoliCi\Build[] $builds A list of buils to remove images from
     */
    public function cleanDirectories($builds = array())
    {
        foreach ($builds as $build) {
            $this->filesystem->remove($build->getDirectory());
        }
    }

    /**
     * Clean images for given builds
     *
     * @param \Joli\JoliCi\Build[] $builds A list of buils to remove images from
     */
    public function cleanContainers($builds = array())
    {
        $images     = array();
        $containers = array();

        foreach ($builds as $build) {
            if (isset($build->getParameters()['image'])) {
                $images[] = $build->getParameters()['image'];
            } else {
                $images[] = $this->docker->getImageManager()->inspect(new Image($build->getRepository(), $build->getTag()));
            }
        }

        foreach ($this->docker->getContainerManager()->findAll(array('all' => 1)) as $container) {
            $id = $container->getConfig()['Image'];

            foreach ($images as $image) {
                if ($image->__toString() == $id) {
                    $containers[] = $container;
                }

                if (preg_match('#^'.$id.'#', $image->getId())) {
                    $containers[] = $container;
                }
            }
        }

        foreach ($containers as $container) {
            $this->docker->getContainerManager()->remove($container, true);
        }
    }

    /**
     * Clean images for given builds
     *
     * @param \Joli\JoliCi\Build[] $builds A list of buils to remove images from
     * @param boolean              $force  Force removal for images
     */
    public function cleanImages($builds = array(), $force = false)
    {
        foreach ($builds as $build) {
            $this->docker->getImageManager()->delete(new Image($build->getRepository(), $build->getTag()), $force);
        }
    }

    /**
     * Get all builds to remove given a project and how many versions to keep
     *
     * @param string $projectPath The project path
     * @param int    $keep        Number of project to keep
     *
     * @return \Joli\JoliCi\Build[] A list of images to remove
     */
    public function getBuildsToRemove($projectPath, $keep = 1)
    {
        $currentBuilds  = $this->strategy->getBuilds($projectPath);
        $existingBuilds = $this->getBuilds($projectPath);
        $uniqList = array();
        $removes  = array();
        $ordered  = array();

        foreach ($currentBuilds as $build) {
            $uniqList[] = $build->getUniq();
        }

        // Remove not existant image (old build configuration)
        foreach ($existingBuilds as $build) {
            if (!in_array($build->getUniq(), $uniqList)) {
                $removes[] = $build;
            } else {
                $ordered[$build->getUniq()][$build->getCreated()->format('U')] = $build;
            }
        }

        // Remove old image
        foreach ($ordered as $builds) {
            ksort($builds);
            $keeped = count($builds);

            while ($keeped > $keep) {
                $removes[] = array_shift($builds);
                $keeped--;
            }
        }

        return $removes;
    }

    /**
     * Get all builds related to a project
     *
     * @param string $projectPath Directory where the project is
     *
     * @return \Joli\JoliCi\Build[]
     */
    protected function getBuilds($projectPath)
    {
        $builds          = array();
        $project         = $this->naming->getProjectName($projectPath);
        $repositoryRegex = sprintf('#^%s_([a-z]+?)/%s$#', Build::BASE_NAME, $project);

        foreach ($this->docker->getImageManager()->findAll() as $image) {
            if (preg_match($repositoryRegex, $image->getRepository(), $matches)) {
                $builds[] = $this->getBuildFromImage($image, $matches[1], $project);
            }
        }

        return $builds;
    }

    /**
     * Create a build from a docker image
     *
     * @param Image  $image
     * @param string $strategy
     * @param string $project
     *
     * @return \Joli\JoliCi\Build
     */
    protected function getBuildFromImage(Image $image, $strategy, $project)
    {
        list($uniq, $timestamp)     = explode('-', $image->getTag());

        return new Build($project, $strategy, $uniq, array('image' => $image), "", \DateTime::createFromFormat('U', $timestamp));
    }
}
