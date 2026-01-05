@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Job #{{ $job->id }}</div>
    <div class="card-body" id="job-details">
        <p>Status: <strong id="job-status">{{ $job->status }}</strong></p>
        <p>Processed {{ $job->processed_files }} / {{ $job->total_files }}</p>
        @if(class_exists('ZipArchive'))
            <a href="{{ route('download.zip', $job) }}" class="btn btn-sm btn-primary">Download ZIP</a>
        @else
            <span class="badge bg-warning text-dark">ZIP desteklenmiyor</span>
        @endif
        <table class="table table-sm mt-3">
            <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Download</th>
                <th>Error</th>
            </tr>
            </thead>
            <tbody id="files-body">
            @foreach($job->files as $file)
                <tr>
                    <td>{{ $file->id }}</td>
                    <td>{{ $file->status }}</td>
                    <td>
                        @if($file->processed_path)
                            <a href="{{ route('download.file', $file) }}" class="btn btn-sm btn-outline-success">Download</a>
                        @endif
                    </td>
                    <td>{{ $file->error_message }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    setInterval(() => {
        fetch('{{ route('jobs.poll', $job) }}')
            .then(r => r.json())
            .then(data => {
                document.getElementById('job-status').innerText = data.status;
                const body = document.getElementById('files-body');
                body.innerHTML = '';
                data.files.forEach(file => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${file.id}</td>
                        <td>${file.status}</td>
                        <td>${file.processed_path ? '<a href="/download/'+file.id+'" class="btn btn-sm btn-outline-success">Download</a>' : ''}</td>
                        <td>${file.error_message ?? ''}</td>`;
                    body.appendChild(row);
                });
            });
    }, 5000);
</script>
@endsection
