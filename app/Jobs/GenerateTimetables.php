<?php

namespace App\Jobs;

use App\Models\Timetable;
use App\Services\GeneticAlgorithm\TimetableGA;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GenerateTimetables implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Timetable $timetable;

    public $timeout = 0; // No timeout — runs until finished

    /**
     * Create a new job instance.
     */
    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $id = $this->timetable->id;

        try {
            Log::info("[GenerateTimetables Job] Starting generation for timetable ID: $id");

            $timetableGA = new TimetableGA($this->timetable);
            $timetableGA->run();

            Log::info("[GenerateTimetables Job] Completed generation for timetable ID: $id");
        } catch (\Throwable $th) {
            Log::error("[GenerateTimetables Job] Error generating timetable ID $id: " . $th->getMessage(), [
                'timetable_id' => $id,
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
