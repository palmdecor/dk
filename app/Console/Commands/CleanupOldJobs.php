<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Job;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupOldJobs extends Command
{
    protected $signature = 'app:cleanup-old-jobs';
    protected $description = 'Remove jobs older than seven days along with files';

    public function handle(): int
    {
        $threshold = Carbon::now()->subDays(7);
        $jobs = Job::where('created_at', '<', $threshold)->get();
        foreach ($jobs as $job) {
            foreach ($job->files as $file) {
                if ($file->original_path) {
                    Storage::delete($file->original_path);
                }
                if ($file->processed_path) {
                    Storage::delete($file->processed_path);
                }
            }
            $job->delete();
        }

        $this->info('Cleanup completed for ' . $jobs->count() . ' jobs');
        return self::SUCCESS;
    }
}
