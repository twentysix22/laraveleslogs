<?php

namespace Twentysix22\LaravelESLogs\Services\Logging\Requests;

use Illuminate\Support\Str;
use Twentysix22\LaravelESLogs\Services\Logging\LoggingRedactor;
use Twentysix22\LaravelESLogs\Services\Logging\Report as AbstractReport;
use Twentysix22\LaravelESLogs\Services\Logging\TracksDuration;
use Twentysix22\LaravelESLogs\Services\Logging\TracksOutput;
use Twentysix22\LaravelESLogs\Services\Logging\TracksSuccess;
use App\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Report extends AbstractReport
{
    use TracksDuration;
    use TracksSuccess;
    use TracksOutput;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Report constructor.
     *
     * @param Request     $request
     * @param string|null $name
     */
    public function __construct(Request $request, string $name = null)
    {
        parent::__construct();

        $this->request = $request;
        $this->name = $name;
    }

    /**
     * Get the request this report is concerned with.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get the response.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get the report data in an indexable format.
     *
     * @return array
     */
    public function getData(): array
    {
        $path = $this->request->decodedPath();
        $name = $this->name ?? $path;
        $user = $this->request->user();

        /**
         * @var \Illuminate\Routing\Route
         */
        $route = optional($this->request->route());

        return $this->getContextData($name)
            + $this->getOutputData()
            + $this->getSuccessData()
            + $this->getDurationData()
            + $this->getAppData()
            + [
                'created_at'        => $this->formatDateTime($this->getCreatedAt()),
                'display_name'      => $name,
                'user'              => $user->id,
                'ip'                => $this->request->ip(),
                'status'            => $this->response->getStatusCode(),
                'method'            => $this->request->method(),
                'domain'            => $this->request->getHost(),
                'full_url'          => $this->request->fullUrl(),
                'url'               => $this->request->url(),
                'path'              => $path,
                'query'             => $this->request->query(),
                'query_string'      => $this->request->getQueryString(),
                'headers'           => $this->formatHeaders($this->request->header()),
                'request'           => $this->formatRequest($this->request),
                'response'          => $this->formatResponse($this->response),
                'action'            => $route->getActionName(),
                'action_method'     => $route->getActionMethod(),
            ];
    }

    /**
     * Provide a name for the report, which will be used as part of the ElasticSearch index name.
     *
     * @return string
     */
    public function getCollection(): string
    {
        return 'requests';
    }

    /**
     * Set the response.
     *
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        // Allow a custom success status to be set, e.g. in the controller, and not be overridden
        if (is_null($this->getSuccess())) {
            $this->setSuccess($response->isSuccessful());
        }

        // Allow a custom exception to be set and not be overridden
        if (isset($response->exception) && is_null($this->getException())) {
            $this->setException($response->exception);
        }

        return $this;
    }

    /**
     * Format the request, including a pretty printed body.
     *
     * @param Request $request
     * @return string
     */
    protected function formatRequest(Request $request): string
    {
        $cookieHeader = '';
        $cookies = [];

        foreach ($request->cookies as $k => $v) {
            $cookies[] = $k.'='.$v;
        }

        if (! empty($cookies)) {
            $cookieHeader = 'Cookie: '.implode('; ', $cookies).PHP_EOL;
        }

        $requestContent = LoggingRedactor::redactJson($request->getContent());
        //limit request log length
        $requestContent = Str::limit($requestContent, config('laraveleslogs.max_request'), '...TRUNCATED');
        $requestHeaders = LoggingRedactor::redactHeaderBag($request->headers);

        return
            sprintf('%s %s %s', $request->getMethod(), $request->getRequestUri(), $request->server->get('SERVER_PROTOCOL')).PHP_EOL.
            $cookieHeader.PHP_EOL.
            $requestHeaders.PHP_EOL.
            $this->formatJson($requestContent);
    }

    /**
     * Format the response, including a pretty printed body.
     *
     * @param Response $response
     * @return string
     */
    protected function formatResponse(Response $response): string
    {
        $statusCode = $response->getStatusCode();
        $statusText = Response::$statusTexts[$statusCode] ?? 'Unknown';

        $responseContent = LoggingRedactor::redactJson($response->getContent());
        //limit response log length.
        $responseContent = Str::limit($responseContent, config('laraveleslogs.max_response'), '...TRUNCATED');
        $responseHeaders = LoggingRedactor::redactHeaderBag($response->headers);

        return sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $statusCode, $statusText).PHP_EOL.
            $responseHeaders.PHP_EOL.
            $this->formatJson($responseContent);
    }

    /**
     * Attempt to pretty print the given body as JSON.
     *
     * @param string $body
     * @return string
     */
    protected function formatJson(string $body): string
    {
        $json = json_decode($body);

        if (json_last_error() != JSON_ERROR_NONE) {
            return $body;
        }

        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Format the headers.
     *
     * @param array $headers
     * @return array
     */
    protected function formatHeaders(array $headers): array
    {
        return array_map(function (array $headers) {
            return implode('; ', $headers);
        }, $headers);
    }

    /**
     * @param User|null $user
     * @return array
     */
    protected function formatUser(?User $user): array
    {
        if ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
            ];
        }

        return [];
    }
}
