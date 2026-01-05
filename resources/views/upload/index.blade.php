@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Bulk Upload</div>
    <div class="card-body">
        <p>Accepted types: jpg, jpeg, png, webp. Max upload: {{ $settings->max_upload_mb ?? 10 }} MB per file.</p>
        <form method="POST" action="{{ route('upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Select files</label>
                <input type="file" name="files[]" class="form-control" multiple accept="image/*">
            </div>
            <button class="btn btn-primary">Start Upload</button>
        </form>
    </div>
</div>
@endsection
