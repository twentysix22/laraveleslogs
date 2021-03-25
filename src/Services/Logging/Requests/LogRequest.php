<?php

namespace Twentysix22\LaravelESLogs\Services\Logging\Requests;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Twentysix22\LaravelESLogs\Services\Logging\Manager;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string|null $requestName Name to be used to identify the request in the logs
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next, ?string $requestName = null)
    {
        // Skip middleware if logging requests is disabled
        if (! config('laraveleslogs.requests')) {
            return $next($request);
        }

        // Create a variable to hold the response to prevent
        // it from being lost on exceptions
        $response = null;

        try {
            /**
             * @var Manager
             */
            $manager = resolve('logging.requests');

            /**
             * @var Report
             */
            $report = new Report($request, $requestName);

            // Start logging
            $manager->addReport($report);
            $report->markStarted();

            /**
             * @var Response
             */
            $response = $next($request);

            // Complete and submit report
            $report
                ->markFinished()
                ->setResponse($response);

            $manager->submitReport();
        } catch (Throwable $e) {
            // Ensure exceptions caused by logging don't disrupt requests
            report($e);
        } finally {
            // Return response in both cases
            return $response;
        }
    }
}
