@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Jobs</h5>
                <p class="display-6">{{ $stats['total_jobs'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Completed</h5>
                <p class="display-6">{{ $stats['completed_jobs'] }}</p>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">Recent Jobs</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Total</th>
                <th>Processed</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($recentJobs as $job)
                <tr>
                    <td>{{ $job->id }}</td>
                    <td>{{ $job->status }}</td>
                    <td>{{ $job->total_files }}</td>
                    <td>{{ $job->processed_files }}</td>
                    <td><a href="{{ route('jobs.show', $job) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
