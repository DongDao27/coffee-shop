<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class LabHeartbeatJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public bool $simulateFailure = false) {}

    public function handle(): void
    {
        if ($this->simulateFailure) {
            throw new RuntimeException('Intentional lab queue failure');
        }
        Log::info('Lab scheduler is running', ['job' => self::class]);
    }
}
