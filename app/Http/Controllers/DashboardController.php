<?php

namespace App\Http\Controllers;

use App\Models\Job;

class DashboardController extends Controller
{
    public function index()
    {
        $recentJobs = Job::orderByDesc('created_at')->limit(5)->get();
        $stats = [
            'total_jobs' => Job::count(),
            'completed_jobs' => Job::where('status', 'completed')->count(),
        ];
        return view('dashboard.index', compact('recentJobs', 'stats'));
    }
}
