<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\WatermarkService;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index(WatermarkService $watermarkService)
    {
        $settings = Setting::firstOrCreate([], [
            'position_mode' => 'bottom_right',
            'scale_mode' => 'percent',
            'scale_value' => 20,
            'opacity' => 80,
            'padding' => 10,
            'max_upload_mb' => 10,
            'output_quality' => 90,
        ]);

        return view('settings.index', [
            'settings' => $settings,
            'imagick' => $watermarkService->imagickAvailable(),
        ]);
    }

    public function update(Request $request, WatermarkService $watermarkService)
    {
        $settings = Setting::first();
        $data = $request->validate([
            'position_mode' => 'required|string',
            'custom_x' => 'nullable|integer',
            'custom_y' => 'nullable|integer',
            'scale_mode' => 'required|string',
            'scale_value' => 'required|integer|min:1',
            'opacity' => 'required|integer|min:0|max:100',
            'padding' => 'required|integer|min:0',
            'max_upload_mb' => 'required|integer|min:1',
            'output_quality' => 'required|integer|min:10|max:100',
            'watermark' => 'nullable|image|mimes:png',
        ]);

        if ($request->hasFile('watermark')) {
            $path = $request->file('watermark')->store('watermarks');
            $data['watermark_path'] = $path;
        }

        $settings->update($data);

        return redirect()->route('settings')->with('status', 'Settings updated');
    }

    public function preview(Request $request, WatermarkService $watermarkService)
    {
        $request->validate([
            'preview_image' => 'required|image|mimes:jpeg,jpg,png,webp',
        ]);

        $settings = Setting::first();
        $tempOriginal = $request->file('preview_image')->store('previews');
        $processed = $watermarkService->applyWatermark($tempOriginal, $settings);

        $url = Storage::url($processed);
        return back()->with('preview', $url);
    }
}
