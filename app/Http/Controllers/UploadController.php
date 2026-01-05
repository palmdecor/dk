<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageJob;
use App\Models\Job;
use App\Models\JobFile;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('upload.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $settings = Setting::first();
        $maxSize = ($settings->max_upload_mb ?? 10) * 1024;

        $request->validate([
            'files.*' => "required|image|mimes:jpeg,jpg,png,webp|max:$maxSize",
        ]);

        $files = $request->file('files');
        $job = Job::create([
            'user_id' => Auth::id(),
            'status' => 'processing',
            'total_files' => count($files),
            'processed_files' => 0,
        ]);

        foreach ($files as $file) {
            $path = $file->store('jobs');
            $jobFile = JobFile::create([
                'job_id' => $job->id,
                'original_path' => $path,
                'status' => 'pending',
            ]);
            ProcessImageJob::dispatch($jobFile->id);
        }

        return redirect()->route('jobs.show', $job);
    }
}
