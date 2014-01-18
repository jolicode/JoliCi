<?php

use Joli\JoliCi\Command\RunCommand;
use Symfony\Component\Console\Application;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once(__DIR__ . '/../vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    require_once(__DIR__ . '/../../../../vendor/autoload.php');
} else {
    throw new \Exception('Unable to load autoloader');
}

$application = new Application('jolici');
$application->add(new RunCommand(__DIR__.'/../resources'));

$application->run();
