<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Carbon\Carbon;
use Elasticsearch;
use Exception;
use Illuminate\Support\Collection;

class Manager
{
    const CONTAINER_TAG = 'logging-managers';

    /**
     * @var Elasticsearch\Client
     */
    public $client;

    /**
     * Stack of reports with the currently active one last.
     *
     * @var Report[]
     */
    protected $reportStack = [];

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $client = Elasticsearch\ClientBuilder::create()
            ->setHosts(config('laraveleslogs.elasticsearch.hosts'))
            ->build();
        $this->client = $client;
    }

    /**
     * Add a new report.
     *
     * @param Report $report
     * @return $this
     */
    public function addReport(Report $report)
    {
        $this->reportStack[] = $report;

        return $this;
    }

    /**
     * Submit the currently active report.
     *
     * @return Report
     * @throws Exception
     */
    public function submitReport(): Report
    {
        $report = $this->popReport();
        $this->sendReport($report);

        return $report;
    }

    /**
     * Discard the currently active report.
     *
     * @return $this
     */
    public function discardReport()
    {
        $this->popReport();

        return $this;
    }

    /**
     * Pop the currently active report off the stack.
     *
     * @return Report
     */
    protected function popReport(): Report
    {
        return array_pop($this->reportStack);
    }

    /**
     * Get the currently active report.
     *
     * @return Report|null
     */
    public function getReport(): ?Report
    {
        return array_last($this->reportStack);
    }

    /**
     * Index the given report.
     *
     * @param Report $report
     * @throws Exception
     */
    public function sendReport(Report $report)
    {
        $entry = [
            'index'   => $this->getIndexName($report),
            'client'  => [
                'timeout' => 3,
            ],
            'type'    => '_doc',
            'id'      => $report->getId(),
            'body'    => $report->getData(),
        ];

        //Globally redact the log array before persisting.
        $redacted = LoggingRedactor::redactArray($entry);
        $this->client->index($redacted);
    }

    /**
     * Get the current index name.
     *
     * @param Report $report
     * @return string
     */
    public function getIndexName(Report $report): string
    {
        return sprintf(
            '%sreports-%s-%s',
            config('laraveleslogs.elasticsearch.prefix'),
            $report->getCollection(),
            Carbon::today()->format('Y.m.d')
        );
    }

    /**
     * Get a collection of all report managers registered with the container.
     *
     * @return Collection
     */
    public static function getManagers(): Collection
    {
        return collect(app()->tagged(static::CONTAINER_TAG));
    }

    /**
     * Set the custom report context.
     *
     * @param array $context
     * @return $this
     */
    public function setContext(array $context): Manager
    {
        // Context should only be applied to the report at the top of the stack
        // as contexts between reports may conflict.
        optional($this->getReport())->setContext($context);

        return $this;
    }

    /**
     * Overrides the default context key.
     *
     * @param  string  $name
     * @return $this
     */
    public function setContextName(string $name): Manager
    {
        optional($this->getReport())->setContextName($name);

        return $this;
    }

    /**
     * Add to the custom report context.
     *
     * @param array $context
     * @return $this
     */
    public function addContext(array $context): Manager
    {
        // Context should only be applied to the report at the top of the stack
        // as contexts between reports may conflict.
        optional($this->getReport())->addContext($context);

        return $this;
    }

    /**
     * Set the global report context.
     *
     * @param array $context
     * @return $this
     */
    public function setGlobalContext(array $context): Manager
    {
        // Context should only be applied to the report at the top of the stack
        // as contexts between reports may conflict.
        optional($this->getReport())->setGlobalContext($context);

        return $this;
    }

    /**
     * Add to the global report context.
     *
     * @param array $context
     * @return $this
     */
    public function addGlobalContext(array $context): Manager
    {
        // Context should only be applied to the report at the top of the stack
        // as contexts between reports may conflict.
        optional($this->getReport())->addGlobalContext($context);

        return $this;
    }

    /**
     * Set the output.
     *
     * @param string $output
     * @return $this
     */
    public function setOutput(string $output): Manager
    {
        foreach ($this->reportStack as $report) {
            if (method_exists($report, 'setOutput')) {
                $report->setOutput($output);
            }
        }

        return $this;
    }

    /**
     * Write a chunk of output.
     *
     * @param string $output
     * @return $this
     */
    public function writeOutput(string $output): Manager
    {
        foreach ($this->reportStack as $report) {
            if (method_exists($report, 'writeOutput')) {
                $report->writeOutput($output);
            }
        }

        return $this;
    }

    /**
     * Set the logged messages collected during the period the report was active.
     *
     * @param string $log
     * @return $this
     */
    public function setLog(string $log): Manager
    {
        foreach ($this->reportStack as $report) {
            if (method_exists($report, 'setLog')) {
                $report->setLog($log);
            }
        }

        return $this;
    }

    /**
     * Write a chunk of log.
     *
     * @param string $log
     * @return $this
     */
    public function writeLog(string $log): Manager
    {
        foreach ($this->reportStack as $report) {
            if (method_exists($report, 'writeLog')) {
                $report->writeLog($log);
            }
        }

        return $this;
    }
}
