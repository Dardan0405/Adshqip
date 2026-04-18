<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogSettings;
use Illuminate\Http\Request;

class ActivityLogSettingsController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.activity-log-settings', [
            'user' => $request->user()->load('profile'),
            'sections' => ActivityLogSettings::sections(),
            'settings' => ActivityLogSettings::getSettings(),
            'enabledCount' => collect(ActivityLogSettings::getSettings())->filter()->count(),
            'totalCount' => count(ActivityLogSettings::allKeys()),
        ]);
    }

    public function update(Request $request)
    {
        $settings = [];

        foreach (array_keys(ActivityLogSettings::allKeys()) as $key) {
            $settings[$key] = $request->boolean('activity_logs.' . $key);
        }

        ActivityLogSettings::store($settings, $request->user()?->id);

        return redirect()
            ->route('admin.activity-log-settings')
            ->with('success', 'Activity log settings updated successfully.');
    }
}
