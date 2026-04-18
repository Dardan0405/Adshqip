<?php

namespace App\Support;

use App\Models\CpmGeoSetting;

class GeoCpmResolver
{
    public function resolve(?string $countryCode): ?float
    {
        $countryCode = strtoupper(trim((string) $countryCode));

        if ($countryCode === '') {
            return null;
        }

        $setting = CpmGeoSetting::query()
            ->where('country_code', $countryCode)
            ->first();

        if (! $setting) {
            return null;
        }

        return round((float) $setting->cpm_value, 4);
    }
}
