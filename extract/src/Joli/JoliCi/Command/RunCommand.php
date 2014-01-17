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

use Docker\Docker;
use Docker\Http\Client;

use Joli\JoliCi\Executor;
use Joli\JoliCi\BuildStrategy\JoliCiBuildStrategy;
use Joli\JoliCi\Log\SimpleFormatter;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Joli\JoliCi\Builder;
use Joli\JoliCi\BuildStrategy\TravisCiBuildStrategy;


class RunCommand extends Command
{
    /**
     * @var Silex\Application
     */
    private $silexApplication;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('run');
        $this->setDescription('Run tests on your project');
        $this->addOption('project-path', 'p', InputOption::VALUE_OPTIONAL, "Path where you project is", ".");
        $this->addOption('no-cache', null, InputOption::VALUE_NONE, "Do not use cache of docker");
        $this->addOption('docker-host', null, InputOption::VALUE_OPTIONAL, "Docker server location", "unix:///var/run/docker.sock");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $quietBuild     = !(OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity());
        $tmpDir         = sys_get_temp_dir().DIRECTORY_SEPARATOR."jolici-builds";
        $logger         = new Logger("standalone-logger");
        $joliciStrategy = new JoliCiBuildStrategy($tmpDir);
        $travisCiStrategy = new TravisCiBuildStrategy($tmpDir);
        $docker         = new Docker(new Client($input->getOption('docker-host')));
        $executor       = new Executor($logger, $docker, !$input->getOption('no-cache'), $quietBuild);
        $filesystem     = new Filesystem();
        $handler        = new StreamHandler("php://stdout");
        $builder        = new Builder();

        $handler->setFormatter(new SimpleFormatter());
        $builder->pushStrategy($joliciStrategy);
        $builder->pushStrategy($travisCiStrategy);
        $logger->pushHandler($handler);

        $output->writeln("<info>Creatings builds...</info>");
        $builds = $builder->createBuilds($input->getOption("project-path"));
        $output->writeln(sprintf("<info>%s builds created</info>", count($builds)));

        foreach ($builds as $build) {
            $output->writeln(sprintf("\n<info>Running build %s</info>\n", $build->getName()));
            $executor->runBuild($build->getDirectory(), $build->getDockerName());
            $executor->runTest($build->getDockerName());

            $filesystem->remove($build->getDirectory());
        }

        if (count($builds) > 0) {
            rmdir(dirname($build->getDirectory()));
        }
    }
}
