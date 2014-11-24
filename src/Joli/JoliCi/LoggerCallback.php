<?php

namespace Joli\JoliCi;

use Psr\Log\LoggerInterface;

class LoggerCallback
{
    private $buildCallback;

    private $runCallback;

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $build = new \ReflectionMethod($this, 'buildCallback');
        $run   = new \ReflectionMethod($this, 'runCallback');

        $this->buildCallback = $build->getClosure($this);
        $this->runCallback   = $run->getClosure($this);
        $this->logger        = $logger;
    }

    /**
     * Get the build log callback when building / pulling an image
     *
     * @return callable
     */
    public function getBuildCallback()
    {
        return $this->buildCallback;
    }

    /**
     * Get the run callback for docker
     *
     * @return callable
     */
    public function getRunCallback()
    {
        return $this->runCallback;
    }

    /**
     * Clear static log buffer
     */
    public function clearStatic()
    {
        $this->logger->debug("", array('clear-static' => true));
    }

    /**
     * The build callback when creating a image, useful to see what happens during building
     *
     * @param string $output An encoded json string from docker daemon
     */
    private function buildCallback($output)
    {
        $message = "";

        if (isset($output['error'])) {
            $this->logger->error(sprintf("Error when creating job: %s", $output['error']), array('static' => false, 'static-id' => null));
            return;
        }

        if (isset($output['stream'])) {
            $message = $output['stream'];
        }

        if (isset($output['status'])) {
            $message = $output['status'];

            if (isset($output['progress'])) {
                $message .= " " . $output['progress'];
            }
        }

        $this->logger->debug($message, array(
            'static' => isset($output['id']),
            'static-id' => isset($output['id']) ? $output['id'] : null,
        ));
    }

    /**
     * Run callback to catch logs of test running
     *
     * @param string $output Output from run
     * @param int $type Type of output (2 = Error, 1 = Stdin, 0 = Stdout)
     */
    private function runCallback($output, $type)
    {
        if ($type === 2) {
            $this->logger->error($output);
        } else {
            $this->logger->info($output);
        }
    }
}
