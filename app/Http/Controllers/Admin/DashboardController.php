<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $applications = LoanApplication::with('user')
            ->latest()
            ->paginate(10);

        return view('admin.dashboard', compact('applications'));
    }

    public function approve(LoanApplication $application): RedirectResponse
    {
        $application->update(['status' => LoanApplication::STATUS_APPROVED]);

        return back()->with('status', __('application.approved'));
    }

    public function reject(LoanApplication $application): RedirectResponse
    {
        $application->update(['status' => LoanApplication::STATUS_REJECTED]);

        return back()->with('status', __('application.rejected'));
    }
}
