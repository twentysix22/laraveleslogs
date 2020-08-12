<?php

namespace Twentysix22\LaravelESLogs\Services\Logging\Jobs;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Throwable;
use Twentysix22\LaravelESLogs\Services\Logging\Manager;

class LogJob
{
    /**
     * Register this listener with the appropriate events.
     */
    public function subscribe()
    {
        Queue::before(static::class);
        Queue::after(static::class);
        Queue::failing(static::class);
    }

    /**
     * Handle the event.
     *
     * @param  JobProcessing|JobProcessed|JobFailed $event
     * @return void
     */
    public function handle($event)
    {
        $manager = resolve('logging.jobs');

        try {
            if ($event instanceof JobProcessing) {
                $this->startReport($manager, $event);
            } elseif ($event instanceof JobProcessed) {
                $this->logSuccess($manager, $event);
            } elseif ($event instanceof JobFailed) {
                $this->logFailure($manager, $event);
            } elseif ($event instanceof JobExceptionOccurred) {
                // Handle a job which has thrown an exception but not yet reached its final attempt
                if (config('logging.job_attempts')) {
                    $this->logFailure($manager, $event);
                } else {
                    $manager->discardReport();
                }
            }
        } catch (Throwable $e) {
            // Ignore exceptions as to not have reporting affect the execution of jobs
            report($e);
        }
    }

    protected function startReport(Manager $manager, JobProcessing $event)
    {
        $report = new Report($event->job);
        $manager->addReport($report);
        $report->markStarted();
    }

    protected function logSuccess(Manager $manager, JobProcessed $event)
    {
        $manager
            ->getReport()
            ->markFinished()
            ->setSuccess(true);

        $manager->submitReport();
    }

    protected function logFailure(Manager $manager, $event)
    {
        $manager
            ->getReport()
            ->markFinished()
            ->setSuccess(false)
            ->setException($event->exception);

        $manager->submitReport();
    }
}
