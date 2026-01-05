<?php

namespace App\Jobs;

use App\Models\JobFile;
use App\Models\Setting;
use App\Services\WatermarkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $jobFileId)
    {
    }

    public function handle(WatermarkService $watermarkService): void
    {
        $jobFile = JobFile::find($this->jobFileId);
        if (!$jobFile) {
            return;
        }

        $jobFile->update(['status' => 'processing']);

        $settings = Setting::first();
        if (!$settings || !$settings->watermark_path) {
            $jobFile->update(['status' => 'failed', 'error_message' => 'Watermark missing']);
            return;
        }

        try {
            $processedPath = $watermarkService->applyWatermark($jobFile->original_path, $settings);
            $jobFile->update([
                'processed_path' => $processedPath,
                'status' => 'done',
            ]);

            $job = $jobFile->job;
            $job->increment('processed_files');
            if ($job->processed_files >= $job->total_files) {
                $job->update(['status' => 'completed']);
            }
        } catch (\Throwable $e) {
            $jobFile->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $job = $jobFile->job;
            $job->update(['status' => 'failed']);
        }
    }
}
