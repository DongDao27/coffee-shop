<?php

namespace Tests\Feature;

use App\Jobs\LabHeartbeatJob;
use App\Models\Order;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BackgroundJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_notification_is_queued(): void
    {
        Bus::fake();
        $order = Order::factory()->create();
        Notification::route('mail', $order->email)->notify(new OrderCreatedNotification($order));
        Bus::assertDispatched(SendQueuedNotifications::class);
    }

    public function test_heartbeat_command_dispatches_requested_job(): void
    {
        Bus::fake();
        $this->artisan('lab:heartbeat --queue')->assertSuccessful();
        Bus::assertDispatched(LabHeartbeatJob::class, fn ($job) => ! $job->simulateFailure);
    }

    public function test_demo_job_raises_reportable_failure(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Intentional lab queue failure');
        (new LabHeartbeatJob(true))->handle();
    }
}
