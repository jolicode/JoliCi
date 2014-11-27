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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RunCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Run tests on your project');
        $this->addOption('project-path', 'p', InputOption::VALUE_OPTIONAL, "Path where you project is (default to current directory)", ".");
        $this->addOption('keep', 'k', InputOption::VALUE_OPTIONAL, "Number of images / containers / directories per build to keep when cleaning at the end of run", 1);
        $this->addOption('no-cache', null, InputOption::VALUE_NONE, "Do not use cache of docker");
        $this->addOption('timeout', null, InputOption::VALUE_OPTIONAL, "Timeout for docker client in seconds (default to 5 minutes)", "300");
        $this->addArgument('cmd', InputArgument::OPTIONAL, "Override test command");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container  = new Container();
        $verbose    = (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity());
        $strategy   = $container->getChainStrategy();
        $executor   = $container->getExecutor(!$input->getOption('no-cache'), $verbose, $input->getOption('timeout'));
        $serviceManager = $container->getServiceManager($verbose);

        $output->writeln("<info>Creating builds...</info>");

        $jobs = $strategy->getJobs($input->getOption("project-path"));

        $output->writeln(sprintf("<info>%s builds created</info>", count($jobs)));

        $exitCode = 0;

        try {
            foreach ($jobs as $job) {
                $output->writeln(sprintf("\n<info>Running job %s</info>\n", $job->getDescription()));

                $serviceManager->start($job);
                $strategy->prepareJob($job);
                $exitCode += $executor->test($job, $input->getArgument('cmd')) == 0 ? 0 : 1;

                $serviceManager->stop($job);
            }
        } catch (\Exception $e) {
            // Try stop last builds
            if (isset($job)) {
                $serviceManager->stop($job);
            }
            // We do not deal with exception (Console Component do it well), we just catch it to allow cleaner to be runned even if one of the build failed hard
        }

        $container->getVacuum()->clean($input->getOption("project-path"), $input->getOption("keep"));

        if (isset($e)) {
            throw $e;
        }

        return $exitCode;
    }
}
