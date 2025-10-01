@extends('layouts.app')

@section('title', __('dashboard.title'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-semibold text-primary">{{ __('dashboard.welcome', ['name' => auth()->user()->name]) }}</h1>
            <p class="text-muted mb-0">{{ __('dashboard.subtitle') }}</p>
        </div>
        <a href="{{ route('applications.create') }}" class="btn btn-primary">{{ __('dashboard.new_application') }}</a>
    </div>

    @if(session('status'))
        <x-alert type="success">{{ session('status') }}</x-alert>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h5 fw-semibold mb-3">{{ __('dashboard.recent_applications') }}</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('dashboard.table.reference') }}</th>
                            <th>{{ __('dashboard.table.amount') }}</th>
                            <th>{{ __('dashboard.table.term') }}</th>
                            <th>{{ __('dashboard.table.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>#{{ $application->id }}</td>
                                <td>{{ format_currency($application->loan_amount) }}</td>
                                <td>{{ $application->loan_term }} {{ __('dashboard.table.months') }}</td>
                                <td>
                                    <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ __('application.status_'.$application->status) }}
                                    </span>
                                </td>
                                <td><a href="{{ route('applications.show', $application) }}" class="btn btn-sm btn-outline-primary">{{ __('dashboard.table.view') }}</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('dashboard.no_applications') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection
