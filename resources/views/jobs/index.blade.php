@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Jobs</div>
    <div class="card-body">
        <table class="table table-striped">
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
            @foreach($jobs as $job)
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
        {{ $jobs->links() }}
    </div>
</div>
@endsection
