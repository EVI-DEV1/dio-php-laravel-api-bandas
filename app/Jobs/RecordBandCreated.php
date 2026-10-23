<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class RecordBandCreated implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $bandId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('band_creation_logs')->insertOrIgnore([
            'band_id' => $this->bandId,
            'created_at' => now(),
        ]);
    }
}
