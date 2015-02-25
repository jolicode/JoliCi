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
        $builds = $this->getJobsToRemove($projectPath, $keep);

        $this->cleanDirectories($builds);
        $this->cleanContainers($builds);
        $this->cleanImages($builds, $force);
    }

    /**
     * Clean directories for given builds
     *
     * @param \Joli\JoliCi\Job[] $jobs A list of jobs to remove images from
     */
    public function cleanDirectories($jobs = array())
    {
        foreach ($jobs as $job) {
            $this->filesystem->remove($job->getDirectory());
        }
    }

    /**
     * Clean images for given builds
     *
     * @param \Joli\JoliCi\Job[] $jobs A list of jobs to remove images from
     */
    public function cleanContainers($jobs = array())
    {
        $images     = array();
        $containers = array();

        foreach ($jobs as $job) {
            if (isset($job->getParameters()['image'])) {
                $images[] = $job->getParameters()['image'];
            } else {
                $images[] = $this->docker->getImageManager()->inspect(new Image($job->getRepository(), $job->getTag()));
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
     * @param \Joli\JoliCi\Job[] $jobs   A list of jobs to remove images from
     * @param boolean            $force  Force removal for images
     */
    public function cleanImages($jobs = array(), $force = false)
    {
        foreach ($jobs as $job) {
            $this->docker->getImageManager()->remove(new Image($job->getRepository(), $job->getTag()), $force);
        }
    }

    /**
     * Get all jobs to remove given a project and how many versions to keep
     *
     * @param string $projectPath The project path
     * @param int    $keep        Number of project to keep
     *
     * @return \Joli\JoliCi\Job[] A list of jobs to remove
     */
    public function getJobsToRemove($projectPath, $keep = 1)
    {
        $currentJobs  = $this->strategy->getJobs($projectPath);
        $existingJobs = $this->getJobs($projectPath);
        $uniqList = array();
        $removes  = array();
        $ordered  = array();

        foreach ($currentJobs as $job) {
            $uniqList[] = $job->getUniq();
        }

        // Remove not existant image (old build configuration)
        foreach ($existingJobs as $job) {
            if (!in_array($job->getUniq(), $uniqList)) {
                $removes[] = $job;
            } else {
                $ordered[$job->getUniq()][$job->getCreated()->format('U')] = $job;
            }
        }

        // Remove old image
        foreach ($ordered as $jobs) {
            ksort($jobs);
            $keeped = count($jobs);

            while ($keeped > $keep) {
                $removes[] = array_shift($jobs);
                $keeped--;
            }
        }

        return $removes;
    }

    /**
     * Get all jobs related to a project
     *
     * @param string $projectPath Directory where the project is
     *
     * @return \Joli\JoliCi\Job[]
     */
    protected function getJobs($projectPath)
    {
        $jobs            = array();
        $project         = $this->naming->getProjectName($projectPath);
        $repositoryRegex = sprintf('#^%s_([a-z]+?)/%s$#', Job::BASE_NAME, $project);

        foreach ($this->docker->getImageManager()->findAll() as $image) {
            if (preg_match($repositoryRegex, $image->getRepository(), $matches)) {
                $jobs[] = $this->getJobFromImage($image, $matches[1], $project);
            }
        }

        return $jobs;
    }

    /**
     * Create a job from a docker image
     *
     * @param Image  $image
     * @param string $strategy
     * @param string $project
     *
     * @return \Joli\JoliCi\Job
     */
    protected function getJobFromImage(Image $image, $strategy, $project)
    {
        list($uniq, $timestamp)     = explode('-', $image->getTag());

        return new Job($project, $strategy, $uniq, array('image' => $image), "", \DateTime::createFromFormat('U', $timestamp));
    }
}
