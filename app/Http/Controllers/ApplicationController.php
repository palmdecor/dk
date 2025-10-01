<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanApplicationRequest;
use App\Mail\ApplicationSubmitted;
use App\Models\LoanApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function dashboard(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $applications = $user->loanApplications()->latest()->paginate(6);

        return view('pages.dashboard', compact('applications'));
    }

    public function create(): View
    {
        return view('pages.application-create');
    }

    public function store(LoanApplicationRequest $request): RedirectResponse
    {
        $application = $request->user()->loanApplications()->create($request->validated());

        Mail::to($request->user()->email)->send(new ApplicationSubmitted($application));

        return redirect()->route('applications.show', $application)
            ->with('status', __('application.submitted'));
    }

    public function show(LoanApplication $application): View
    {
        $this->authorize('view', $application);

        return view('pages.application-show', compact('application'));
    }
}
