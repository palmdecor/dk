@extends('layouts.app')

@section('title', __('admin.title'))

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-semibold text-primary">{{ __('admin.heading') }}</h1>
            <p class="text-muted mb-0">{{ __('admin.subtitle') }}</p>
        </div>
        <span class="badge bg-primary">CRM</span>
    </div>

    @if(session('status'))
        <x-alert type="success">{{ session('status') }}</x-alert>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('admin.table.customer') }}</th>
                            <th>{{ __('admin.table.amount') }}</th>
                            <th>{{ __('admin.table.term') }}</th>
                            <th>{{ __('admin.table.status') }}</th>
                            <th>{{ __('admin.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>{{ $application->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $application->user->name }}</div>
                                    <small class="text-muted">{{ $application->email }}</small>
                                </td>
                                <td>{{ format_currency($application->loan_amount) }}</td>
                                <td>{{ $application->loan_term }} {{ __('dashboard.table.months') }}</td>
                                <td>
                                    <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : 'warning') }}">{{ __('application.status_'.$application->status) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('admin.applications.approve', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-success" @if($application->status === 'approved') disabled @endif>{{ __('admin.table.approve') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.applications.reject', $application) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-danger" @if($application->status === 'rejected') disabled @endif>{{ __('admin.table.reject') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">{{ __('admin.no_applications') }}</td>
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
