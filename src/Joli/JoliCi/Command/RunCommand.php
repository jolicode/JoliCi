<?php
/*
 * This file is part of JoliCi.
*
* (c) Joel Wurtz <jwurtz@jolicode.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Joli\JoliCi\Command;

use Joli\JoliCi\Container;
use Joli\JoliCi\Filesystem\Filesystem;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


class RunCommand extends Command
{
    /**
     * @var string Base path for resources
     */
    private $resourcesPath;

    public function __construct($resourcesPath)
    {
        parent::__construct();

        $this->resourcesPath = $resourcesPath;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $defaultDockerHost = getenv('DOCKER_HOST') ? sprintf("tcp://%s:4243", getenv('DOCKER_HOST')) : "unix:///var/run/docker.sock";

        $this->setName('run');
        $this->setDescription('Run tests on your project');
        $this->addOption('project-path', 'p', InputOption::VALUE_OPTIONAL, "Path where you project is", ".");
        $this->addOption('no-cache', null, InputOption::VALUE_NONE, "Do not use cache of docker");
        $this->addOption('timeout', null, InputOption::VALUE_OPTIONAL, "Timeout for docker client in seconds (default to 5 minutes)", "300");
        $this->addOption('docker-host', null, InputOption::VALUE_OPTIONAL, "Docker server location", $defaultDockerHost);
        $this->addArgument('cmd', InputArgument::OPTIONAL, "Override test command");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container  = new Container();
        $quiet      = !(OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity());
        $builder    = $container->getBuilder();
        $executor   = $container->getExecutor($input->getOption('docker-host'), !$input->getOption('no-cache'), $quiet, $input->getOption('timeout'));
        $filesystem = new Filesystem();

        $output->writeln("<info>Creating builds...</info>");
        $builds = $builder->createBuilds($input->getOption("project-path"));
        $output->writeln(sprintf("<info>%s builds created</info>", count($builds)));

        foreach ($builds as $build) {
            $output->writeln(sprintf("\n<info>Running build %s</info>\n", $build->getName()));

            if ($executor->runBuild($build->getDirectory(), $build->getDockerName())) {
                $executor->runTest($build->getDockerName(), $input->getArgument('cmd'));
            }

            $filesystem->remove($build->getDirectory());
        }

        //Remove parent folder
        if (count($builds) > 0) {
            rmdir(dirname($build->getDirectory()));
        }
    }
}
