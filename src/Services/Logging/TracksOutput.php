<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Exception;

trait TracksOutput
{
    /**
     * @var string
     */
    protected $output = '';

    /**
     * @var string
     */
    protected $log = '';

    /**
     * Set the output.
     *
     * @param string $output
     * @return $this
     */
    public function setOutput(string $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Write a chunk of output.
     *
     * @param string $output
     * @return $this
     */
    public function writeOutput(string $output)
    {
        $this->output .= $output.PHP_EOL;

        return $this;
    }

    /**
     * Set the logged messages collected during the period the report was active.
     *
     * @param string $log
     * @return $this
     */
    public function setLog(string $log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Write a chunk of log.
     *
     * @param string $log
     * @return $this
     */
    public function writeLog(string $log)
    {
        $this->log .= $log.PHP_EOL;

        return $this;
    }

    /**
     * Get success data.
     *
     * @return array
     */
    public function getOutputData(): array
    {
        return [
            'output' => $this->output,
            'log'    => $this->log,
        ];
    }
}
