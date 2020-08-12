<?php

namespace Twentysix22\LaravelESLogs\Commands;

use Carbon\Carbon;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class Tidy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laraveleslogs:tidy {prefix?} {--days=} {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tidy up by purging old indices';

    protected $indexPatterns = [
        '{prefix}reports-jobs-{date}',
        '{prefix}reports-requests-{date}',
    ];

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
        $days = $this->option('days') ?? config('laraveleslogs.elasticsearch.keep_days');

        if (is_null($days)) {
            $this->error('Number of days to keep must be configured or specified.');

            return 1;
        }

        $client = ClientBuilder::create()
            ->setHosts(config('laraveleslogs.elasticsearch.hosts'))
            ->build();

        $this->purgeIndices($client, $prefix, (int) $days);
        $this->line('Indices tidied up.');
    }

    /**
     * Purge old indices.
     *
     * @param Client $client
     * @param string $prefix
     * @param int $days
     */
    protected function purgeIndices(Client $client, string $prefix, int $days)
    {
        foreach ($this->indexPatterns as $pattern) {
            $indexPatterns = $this->renderIndexPatterns($prefix, $pattern, $days);
            $client->indices()->delete([
                'index' => implode(',', $indexPatterns),
                'ignore_unavailable' => true,
            ]);
        }
    }

    /**
     * Produce the appropriate index patterns for the given prefix, pattern and number of days to keep.
     *
     * @param string $prefix
     * @param string $indexPattern
     * @param int $days
     * @return array
     */
    protected function renderIndexPatterns(string $prefix, string $indexPattern, int $days)
    {
        $indexPattern = str_replace('{prefix}', $prefix, $indexPattern);

        // Delete all indices matching the pattern…
        $renderedPatterns = [
            str_replace('{date}', '*', $indexPattern),
        ];

        $date = Carbon::today();
        $target = $date->copy()->subDays($days);

        while ($date > $target) {
            if ($date->diffInYears($target)) {
                // Date differs by at least a year, exclude current year
                $datePattern = $date->format('Y.*.*');

                // Set date to end of previous year
                $date
                    ->subYearNoOverflow()
                    ->endOfYear()
                    ->startOfDay();
            } elseif ($date->diffInMonths($target)) {
                // Date differs by at least a month, exclude current month
                $datePattern = $date->format('Y.m.*');

                // Set date to end of previous month
                $date
                    ->subMonthNoOverflow()
                    ->endOfMonth()
                    ->startOfDay();
            } else {
                // Date differs by days, exclude current day
                $datePattern = $date->format('Y.m.d');

                // Set date to previous day
                $date->subDay();
            }

            // "-" at the beginning of the pattern inverts it and acts as an exclusion
            $renderedPatterns[] = '-'.str_replace('{date}', $datePattern, $indexPattern);
        }

        // E.g. "prd_foobar-*", "-prd_foobar-2019.*.*", "-prd_foobar-2018.12.*", "-prd_foobar-2018.11.30"…
        return $renderedPatterns;
    }
}
