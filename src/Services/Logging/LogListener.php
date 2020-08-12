<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Carbon\Carbon;
use Illuminate\Log\Events\MessageLogged;

class LogListener
{
    /**
     * Listen to log events to capture logs emitted during a job's runtime.
     *
     * @param MessageLogged $event
     */
    public function handle(MessageLogged $event)
    {
        $logLine = sprintf(
            '[%s] %s: %s',
            Carbon::now()->toDateTimeString(),
            strtoupper($event->level),
            $event->message
        );

        Manager::getManagers()->each->writeLog($logLine);
    }
}
