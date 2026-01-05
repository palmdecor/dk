@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">Settings</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" action="{{ route('settings') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Watermark PNG</label>
                        <input type="file" name="watermark" class="form-control">
                        @if($settings->watermark_path)
                            <small class="text-muted">Current: {{ $settings->watermark_path }}</small>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Position Mode</label>
                            <select class="form-select" name="position_mode">
                                @foreach(['bottom_right','bottom_left','top_right','top_left','center','custom'] as $mode)
                                    <option value="{{ $mode }}" @selected($settings->position_mode === $mode)>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Custom X</label>
                            <input type="number" class="form-control" name="custom_x" value="{{ $settings->custom_x }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Custom Y</label>
                            <input type="number" class="form-control" name="custom_y" value="{{ $settings->custom_y }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Scale Mode</label>
                            <select class="form-select" name="scale_mode">
                                <option value="percent" @selected($settings->scale_mode==='percent')>Percent</option>
                                <option value="px" @selected($settings->scale_mode==='px')>Pixels</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Scale Value</label>
                            <input type="number" class="form-control" name="scale_value" value="{{ $settings->scale_value }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Opacity</label>
                            <input type="number" class="form-control" name="opacity" value="{{ $settings->opacity }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Padding</label>
                            <input type="number" class="form-control" name="padding" value="{{ $settings->padding }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Output Quality</label>
                            <input type="number" class="form-control" name="output_quality" value="{{ $settings->output_quality }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Max Upload MB</label>
                        <input type="number" class="form-control" name="max_upload_mb" value="{{ $settings->max_upload_mb }}">
                    </div>
                    <button class="btn btn-primary">Save</button>
                    <span class="ms-2 badge bg-info">Imagick: {{ $imagick ? 'Available' : 'Fallback to GD' }}</span>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Preview</div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload sample</label>
                        <input type="file" name="preview_image" class="form-control" required>
                    </div>
                    <button class="btn btn-outline-primary">Generate Preview</button>
                </form>
                @if(session('preview'))
                    <div class="mt-3">
                        <img src="{{ session('preview') }}" class="img-fluid" alt="Preview">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
