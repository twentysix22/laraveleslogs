<?php

namespace App\Services\Logging;

use App\Contracts\OutputProvider;
use App\Support\Str;
use App\Support\SupportsOutput;

trait CapturesOutput
{
    use SupportsOutput {
        getOutput as getOriginalOutput;
    }

    public function getOutput(): OutputProvider
    {
        $output = $this->getOriginalOutput();

        return new class($output) implements OutputProvider {
            protected $output;

            public function __construct(OutputProvider $output)
            {
                $this->output = $output;
            }

            public function line(string $string)
            {
                $this->writeOutput($string);

                return $this->output->line($string);
            }

            public function table(array $headers, array $rows)
            {
                $this->writeOutput(
                    Str::table($headers, $rows)
                );

                return $this->output->table($headers, $rows);
            }

            protected function writeOutput(string $string)
            {
                Manager::getManagers()->each->writeOutput($string);
            }
        };
    }
}
