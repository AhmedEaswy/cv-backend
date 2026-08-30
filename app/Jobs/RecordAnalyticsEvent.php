<?php

namespace App\Jobs;

use App\Models\AnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordAnalyticsEvent implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload) {}

    public function handle(): void
    {
        try {
            AnalyticsEvent::create($this->payload);
        } catch (\Throwable) {
            // Never fail the originating request because analytics could not be recorded.
        }
    }
}
