<?php

namespace Twentysix22\LaravelESLogs\Services\Logging\Jobs;

use Twentysix22\LaravelESLogs\Services\Logging\Report as AbstractReport;
use Twentysix22\LaravelESLogs\Services\Logging\TracksDuration;
use Twentysix22\LaravelESLogs\Services\Logging\TracksOutput;
use Twentysix22\LaravelESLogs\Services\Logging\TracksSuccess;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\Job;

class Report extends AbstractReport
{
    use TracksDuration;
    use TracksSuccess;
    use TracksOutput;

    /**
     * @var Job
     */
    protected $job;

    /**
     * Report constructor.
     *
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        parent::__construct();

        $this->job = $job;
    }

    /**
     * Get the job this report is concerned with.
     *
     * @return Job
     */
    public function getJob(): Job
    {
        return $this->job;
    }

    /**
     * Get the report data in an indexable format.
     *
     * @return array
     */
    public function getData(): array
    {
        $payload = $this->job->payload();
        $command = array_get($payload, 'data.commandName');
        $queuedAt = array_get($payload, 'pushedAt');
        $queuedAt = $queuedAt ? Carbon::createFromTimestampMs($queuedAt * 1000) : null;

        return $this->getSuccessData()
            + $this->getDurationData()
            + $this->getContextData($this->name ?? $command)
            + $this->getOutputData()
            + $this->getAppData()
            + [
                'id'                 => $this->job->getJobId(),
                'job'                => $command,
                'display_name'       => array_get($payload, 'displayName'),
                'queue'              => $this->job->getQueue(),
                'connection'         => $this->job->getConnectionName(),
                'type'               => array_get($payload, 'type'),
                'created_at'         => $this->formatDateTime($this->getCreatedAt()),
                'queued_at'          => $this->formatDateTime($queuedAt),
                'queued_to_started'  => $this->floatDiffInSeconds($queuedAt, $this->startedAt),
                'queued_to_finished' => $this->floatDiffInSeconds($queuedAt, $this->finishedAt),
                'attempts'           => (int) array_get($payload, 'attempts', 0) + 1,
                'max_attempts'       => (int) array_get($payload, 'maxTries'),
                // Request ID is provided by App\Queue\RedisQueue
                'request_id'         => array_get($payload, 'requestId'),
                'payload'            => json_encode($payload, JSON_PRETTY_PRINT),
            ];
    }

    /**
     * Determine what to call this collection of reports.
     * This will be used as part of the ElasticSearch index name.
     *
     * @return string
     */
    public function getCollection(): string
    {
        return 'jobs';
    }
}
