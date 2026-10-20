<?php

namespace App\Console\Commands;

use App\Jobs\LabHeartbeatJob;
use Illuminate\Console\Command;

class LabHeartbeat extends Command
{
    protected $signature = 'lab:heartbeat {--queue : Dispatch to the queue} {--fail : Simulate a failure for the queue lab}';

    protected $description = 'Write a heartbeat or dispatch a queue demonstration';

    public function handle(): int
    {
        if ($this->option('queue')) {
            LabHeartbeatJob::dispatch((bool) $this->option('fail'));
            $this->info('Heartbeat job queued.');
        } else {
            LabHeartbeatJob::dispatchSync((bool) $this->option('fail'));
            $this->info('Heartbeat logged.');
        }

        return self::SUCCESS;
    }
}
