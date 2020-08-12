<?php

namespace Twentysix22\LaravelESLogs\Services\Logging\Jobs;

trait ReportsJobContext
{
    public function setContextName(string $name)
    {
        if (! config('laraveleslogs.jobs')) {
            return;
        }

        resolve('logging.jobs')->setContextName($name);
    }

    public function logContext(array $data)
    {
        if (! config('laraveleslogs.jobs')) {
            return;
        }

        resolve('logging.jobs')->addContext($data);
    }

    public function logGlobalContext(array $data)
    {
        if (! config('laraveleslogs.jobs')) {
            return;
        }

        resolve('logging.jobs')->addGlobalContext($data);
    }
}
