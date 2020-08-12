<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Carbon\Carbon;

trait TracksDuration
{
    /**
     * @var Carbon
     */
    protected $startedAt;

    /**
     * @var Carbon
     */
    protected $finishedAt;

    /**
     * Store the start time.
     *
     * @return $this
     */
    public function markStarted()
    {
        $this->startedAt = Carbon::now();

        return $this;
    }

    /**
     * Store the finishing time.
     *
     * @return $this
     */
    public function markFinished()
    {
        $this->finishedAt = Carbon::now();

        return $this;
    }

    /**
     * Get the duration.
     *
     * @return float|null
     */
    public function getDuration(): ?float
    {
        return $this->floatDiffInSeconds($this->startedAt, $this->finishedAt);
    }

    /**
     * Get data on start time, finish time, and duration.
     *
     * @return array
     */
    public function getDurationData(): array
    {
        return [
            'started_at'   => $this->formatDateTime($this->startedAt),
            'finished_at'  => $this->formatDateTime($this->finishedAt),
            'duration'     => $this->getDuration(),
        ];
    }

    /**
     * Calculate the difference in seconds as a float with millisecond precision.
     *
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @return float|null
     */
    protected function floatDiffInSeconds(?Carbon $from, ?Carbon $to): ?float
    {
        if (! $from || ! $to) {
            return null;
        }

        return $from->floatDiffInSeconds($to);
    }
}
