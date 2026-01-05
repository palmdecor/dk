<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobFile;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DownloadController extends Controller
{
    public function downloadFile(JobFile $file)
    {
        abort_unless($file->processed_path, 404);
        return Storage::download($file->processed_path);
    }

    public function downloadZip(Job $job)
    {
        if (!class_exists(ZipArchive::class)) {
            return back()->withErrors(['zip' => 'ZIP desteklenmiyor']);
        }

        $zip = new ZipArchive();
        $zipName = storage_path('app/tmp_' . $job->id . '.zip');
        $zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($job->files()->whereNotNull('processed_path')->get() as $file) {
            $zip->addFile(Storage::path($file->processed_path), basename($file->processed_path));
        }
        $zip->close();

        return response()->download($zipName)->deleteFileAfterSend(true);
    }
}
