<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::orderByDesc('created_at')->paginate(15);
        return view('jobs.index', compact('jobs'));
    }

    public function show(Job $job)
    {
        $job->load('files');
        return view('jobs.show', compact('job'));
    }

    public function poll(Job $job)
    {
        $job->load('files');
        return response()->json($job);
    }
}
