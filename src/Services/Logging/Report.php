<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Ramsey\Uuid\Uuid;

abstract class Report implements Arrayable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * Context is scoped and custom to the category of the object being logged.
     *
     * @var array
     */
    protected $context = [];

    /**
     * Global context is not scoped and therefore searchable across logs of different categories.
     * This however means that the same key must always have the same data type to allow for indexing.
     *
     * @var array
     */
    protected $globalContext = [];

    /**
     * @var Carbon
     */
    protected $created_at;

    /**
     * Create a new report.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->created_at = Carbon::now();
    }

    /**
     * Determine what to call this collection of reports.
     * This will be used as part of the ElasticSearch index name.
     *
     * @return string
     */
    abstract public function getCollection(): string;

    /**
     * Get the report data in an indexable format.
     *
     * @return array
     */
    abstract public function getData(): array;

    /**
     * Get the report's ID, which will be used as the ID of the document in ElasticSearch.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get code version and runtime information.
     *
     * @return array
     */
    public function getAppData(): array
    {
        return [
            'host'  => gethostname(),
            'env'   => config('app.env'),
            'group' => config('laraveleslogs.log_group'),
            'app_name' => config('app.name'),
        ];
    }

    /**
     * Get context data, namespaced with the given value.
     *
     * @var string
     * @return array
     */
    public function getContextData(string $namespace): array
    {
        $context = $this->globalContext;

        if ($this->context) {
            // Prefix the custom context with the given namespace to avoid collisions with indexed attributes of other
            // reports as an attribute with the same name must have the same data type in every ElasticSearch document.
            $context[$namespace] = $this->context;
        }

        return [
            'namespace' => $namespace,
            'context' => $context,
        ];
    }

    /**
     * Format a given date in a ISO8601 compatible format including milliseconds.
     *
     * @param Carbon|null $date
     * @return null|string
     */
    protected function formatDateTime(?Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        return $date->format(Carbon::RFC3339_EXTENDED);
    }

    /**
     * Overrides the default context key.
     *
     * @param  string  $name
     * @return $this
     */
    public function setContextName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the custom report context.
     *
     * @param array $context
     * @return $this
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Add to the custom report context.
     *
     * @param array $context
     * @return $this
     */
    public function addContext(array $context)
    {
        return $this->setContext(
            array_merge($this->context, $context)
        );
    }

    /**
     * Set the global report context.
     *
     * @param array $context
     * @return $this
     */
    public function setGlobalContext(array $context)
    {
        $this->globalContext = $context;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    /**
     * Add to the global report context.
     *
     * @param array $context
     * @return $this
     */
    public function addGlobalContext(array $context)
    {
        return $this->setGlobalContext(
            array_merge($this->globalContext, $context)
        );
    }

    /**
     * Get the report data as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getData();
    }
}
