<?php

namespace Joli\JoliCi;

use Behat\Transliterator\Transliterator;

class Naming
{
    /**
     * Return a translitared name for a project
     *
     * @param string $projectPath Project directory
     *
     * @return string
     */
    public function getProjectName($projectPath)
    {
        $project = basename(realpath($projectPath));
        $project = Transliterator::transliterate($project, '-');

        return $project;
    }

    /**
     * Generate a unique key for a list of parameters, with same parameters
     * the key must not change (no random stuff) and the order does not matter
     *
     * @param array $parameters
     *
     * @return integer
     */
    public function getUniqueKey($parameters = array())
    {
        // First ordering parameters
        ksort($parameters);

        // Return a hash of the serialzed parameters
        return crc32(serialize($parameters));
    }
}
