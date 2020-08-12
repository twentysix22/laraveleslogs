<?php

namespace Twentysix22\LaravelESLogs\Services\Logging\Requests;

trait ReportsRequestContext
{
    public function setContextName(string $name)
    {
        if (! config('laraveleslogs.requests')) {
            return;
        }

        resolve('logging.requests')->setContextName($name);
    }

    public function logContext(array $data)
    {
        if (! config('laraveleslogs.requests')) {
            return;
        }

        resolve('logging.requests')->addContext($data);
    }

    public function logGlobalContext(array $data)
    {
        if (! config('laraveleslogs.requests')) {
            return;
        }

        resolve('logging.requests')->addGlobalContext($data);
    }
}
