<?php

namespace Joli\JoliCi;

use Docker\API\Model\BuildInfo;
use Psr\Log\LoggerInterface;

class LoggerCallback
{
    /**
     * @var \Closure
     */
    private $buildCallback;

    /**
     * @var \Closure
     */
    private $runStdoutCallback;

    /**
     * @var \Closure
     */
    private $runStderrCallback;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $build     = new \ReflectionMethod($this, 'buildCallback');
        $runStdout = new \ReflectionMethod($this, 'runStdoutCallback');
        $runStderr = new \ReflectionMethod($this, 'runStderrCallback');

        $this->buildCallback = $build->getClosure($this);
        $this->runStdoutCallback = $runStdout->getClosure($this);
        $this->runStderrCallback = $runStderr->getClosure($this);
        $this->logger = $logger;
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
     * Get the run stdout callback for docker
     *
     * @return callable
     */
    public function getRunStdoutCallback()
    {
        return $this->runStdoutCallback;
    }

    /**
     * Get the run stderr callback for docker
     *
     * @return callable
     */
    public function getRunStderrCallback()
    {
        return $this->runStderrCallback;
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
     * @param BuildInfo $output An encoded json string from docker daemon
     */
    private function buildCallback(BuildInfo $output)
    {
        $message = "";

        if ($output->getError()) {
            $this->logger->error(sprintf("Error when creating job: %s\n", $output->getError()), array('static' => false, 'static-id' => null));
            return;
        }

        if ($output->getStream()) {
            $message = $output->getStream();
        }

        if ($output->getStatus()) {
            $message = $output->getStatus();

            if ($output->getProgress()) {
                $message .= " " . $output->getProgress();
            }
        }

        // Force new line
        if (!$output->getId() && !preg_match('#\n#', $message)) {
            $message .= "\n";
        }

        $this->logger->debug($message, array(
            'static' => $output->getId() !== null,
            'static-id' => $output->getId(),
        ));
    }

    /**
     * Run callback to catch stdout logs of test running
     *
     * @param string $output Output from run (stdout)
     */
    private function runStdoutCallback($output)
    {
        $this->logger->info($output);
    }

    /**
     * Run callback to catch stderr logs of test running
     *
     * @param string $output Output from run (stderr)
     */
    private function runStderrCallback($output)
    {
        $this->logger->error($output);
    }
}
