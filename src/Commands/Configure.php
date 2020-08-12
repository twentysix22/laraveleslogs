<?php

namespace Twentysix22\LaravelESLogs\Commands;

use Elasticsearch;
use Illuminate\Console\Command;

class Configure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraveleslogs:configure {prefix?} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Logging Elasticsearch indices';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! config('laraveleslogs.elasticsearch.hosts')) {
            $this->warn('No Elasticsearch host configured.');

            return;
        }

        $prefix = $this->argument('prefix') ?? config('laraveleslogs.elasticsearch.prefix');
        $confirmation = sprintf('Are you sure you want to reset settings for all indices with prefix "%s"?', $prefix);

        if (! $this->option('force') && ! $this->confirm($confirmation)) {
            return;
        }
        $client = Elasticsearch\ClientBuilder::create()
            ->setHosts(config('laraveleslogs.elasticsearch.hosts'))
            ->build();

        $this->bootIndices($client, $prefix);
        $this->info('Indices configured.');
    }

    /**
     * Initialise Elasticsearch indices with desired settings.
     *
     * @param Elasticsearch\Client $client
     * @param string  $prefix
     */
    protected function bootIndices(Elasticsearch\Client $client, string $prefix)
    {
        $client->indices()->putTemplate([
            'name' => $prefix.'reports-jobs',
            'order' => 10,
            'body' => [
                'index_patterns' => [$prefix.'reports-jobs-*'],
                'version' => 1,
                'settings' => [
                    'number_of_shards' => 1,
                ],
                'mappings' => [
                    '_doc' => [
                        // Ignore fields that aren't explicitly specified in this schema
                        'dynamic' => false,
                        'properties' => [
                            'id' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'job' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'display_name' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'host' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'env' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'queue' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'connection' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'type' => [
                                'type' => 'text',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword'],
                                ],
                            ],
                            'queued_at' => ['type' => 'date'],
                            'started_at' => ['type' => 'date'],
                            'finished_at' => ['type' => 'date'],
                            'duration' => ['type' => 'float'],
                            'queued_to_started' => ['type' => 'float'],
                            'queued_to_finished' => ['type' => 'float'],
                            'success' => ['type' => 'boolean'],
                            'success_rate' => ['type' => 'byte'],
                            'attempts' => ['type' => 'integer'],
                            'max_attempts' => ['type' => 'integer'],
                            'exception' => [
                                'type' => 'object',
                                'properties' => [
                                    'class' => [
                                        'type' => 'text',
                                        'fields' => [
                                            'keyword' => ['type' => 'keyword'],
                                        ],
                                    ],
                                    'message' => [
                                        'type' => 'text',
                                        'fields' => [
                                            'keyword' => ['type' => 'keyword'],
                                        ],
                                    ],
                                    'code' => [
                                        'type' => 'text',
                                        'fields' => [
                                            'keyword' => ['type' => 'keyword'],
                                        ],
                                    ],
                                    'file' => [
                                        'type' => 'text',
                                        'fields' => [
                                            'keyword' => ['type' => 'keyword'],
                                        ],
                                    ],
                                    'line' => ['type' => 'integer'],
                                    'trace' => ['type' => 'text'],
                                ],
                            ],
                            'payload' => [
                                // Disable indexing of payload
                                'enabled' => false,
                            ],
                            'output' => ['type' => 'text'],
                            'log' => ['type' => 'text'],
                            'context' => [
                                'type' => 'object',
                                // Allow new fields to be implicitly added
                                'dynamic' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $client->indices()->putTemplate([
            'name' => $prefix.'reports-requests',
            'order' => 10,
            'body' => [
                'index_patterns' => [$prefix.'reports-requests-*'],
                'version' => 1,
                'settings' => [
                    'number_of_shards' => 1,
                ],
            ],
        ]);
    }
}
