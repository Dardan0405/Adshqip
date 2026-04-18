<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CpmGeoSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CpmGeoSettingController extends Controller
{
    public function index()
    {
        $settings = CpmGeoSetting::query()
            ->orderBy('country_name')
            ->paginate(20);

        return view('admin.cpm-geo-settings.index', [
            'settings' => $settings,
            'countries' => $this->countries(),
            'stats' => [
                'total_rows' => CpmGeoSetting::count(),
                'average_cpm' => (float) (CpmGeoSetting::avg('cpm_value') ?? 0),
                'highest_cpm' => (float) (CpmGeoSetting::max('cpm_value') ?? 0),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $countries = $this->countries();

        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:2', Rule::in(array_keys($countries)), 'unique:aq_cpm_geo_settings,country_code'],
            'cpm_value' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        CpmGeoSetting::create([
            'country_code' => $validated['country_code'],
            'country_name' => $countries[$validated['country_code']],
            'cpm_value' => $validated['cpm_value'],
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.cpm-geo-settings')
            ->with('success', 'CPM GEO setting created successfully.');
    }

    public function destroy(CpmGeoSetting $cpmGeoSetting)
    {
        $cpmGeoSetting->delete();

        return redirect()
            ->route('admin.cpm-geo-settings')
            ->with('success', 'CPM GEO setting deleted successfully.');
    }

    private function countries(): array
    {
        return [
            'AL' => 'Albania',
            'AT' => 'Austria',
            'BA' => 'Bosnia & Herzegovina',
            'CH' => 'Switzerland',
            'DE' => 'Germany',
            'FR' => 'France',
            'GB' => 'United Kingdom',
            'HR' => 'Croatia',
            'IT' => 'Italy',
            'ME' => 'Montenegro',
            'MK' => 'North Macedonia',
            'RS' => 'Serbia',
            'SI' => 'Slovenia',
            'US' => 'United States',
            'XK' => 'Kosovo',
        ];
    }
}
