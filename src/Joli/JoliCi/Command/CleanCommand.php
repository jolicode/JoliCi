<?php

namespace Joli\JoliCi\Command;

use Joli\JoliCi\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CleanCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('clean');
        $this->setDescription('Clean images, containers and/or directories of previous build for this project');
        $this->addOption('project-path', 'p', InputOption::VALUE_OPTIONAL, "Path where you project is (default to current directory)", ".");
        $this->addOption('keep', 'k', InputOption::VALUE_OPTIONAL, "Number of images / containers / directories per build to keep", 1);
        $this->addOption('only-containers', null, InputOption::VALUE_NONE, "Only clean containers (no images or directories)");
        $this->addOption('only-directories', null, InputOption::VALUE_NONE, "Only clean directories (no images or containers)");
        $this->addOption('only-images', null, InputOption::VALUE_NONE, "Only clean images (no containers or directories), be aware that this may fail if containers are still attached to images (you may need to use force option)");
        $this->addOption('force', null, InputOption::VALUE_NONE, "Force removal for images");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = new Container();
        $vacuum = $container->getVacuum();

        if ($input->getOption('only-containers')) {
            $vacuum->cleanContainers($vacuum->getJobsToRemove($input->getOption('project-path'), $input->getOption('keep')));

            return 0;
        }

        if ($input->getOption('only-directories')) {
            $vacuum->cleanDirectories($vacuum->getJobsToRemove($input->getOption('project-path'), $input->getOption('keep')));

            return 0;
        }

        if ($input->getOption('only-images')) {
            $vacuum->cleanImages($vacuum->getJobsToRemove($input->getOption('project-path'), $input->getOption('keep')), $input->getOption('force'));

            return 0;
        }

        $vacuum->clean($input->getOption('project-path'), $input->getOption('keep'), $input->getOption('force'));

        return 0;
    }
}
