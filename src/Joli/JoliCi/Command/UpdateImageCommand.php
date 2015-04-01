<?php

namespace Joli\JoliCi\Command;

use Joli\JoliCi\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdateImageCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('images-update');
        $this->setDescription('Update docker images used to build test environnement');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = new Container();
        $docker = $container->getDocker();
        $logger = $container->getLoggerCallback((OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()));

        foreach ($docker->getImageManager()->findAll() as $image) {
            if (preg_match('#^jolicode/(.+?)$#', $image->getRepository())) {
                $output->writeln(sprintf("Update %s image", $image->getRepository()));
                $docker->getImageManager()->pull($image->getRepository(), 'latest', $logger->getBuildCallback());
            }
        }
    }
}
