<?php

namespace App\Http\Controllers;

use App\Models\ReferralLink;
use Illuminate\Http\Request;

class ReferralRedirectController extends Controller
{
    public function __invoke(Request $request, string $code)
    {
        $link = ReferralLink::query()
            ->where('code', strtoupper($code))
            ->where('status', 'active')
            ->where('is_deleted', false)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();

        $link->increment('total_clicks');

        $params = [
            'ref_code' => $link->code,
        ];

        if (in_array($link->target_role, ['advertiser', 'publisher'], true)) {
            $params['role'] = $link->target_role;
        }

        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $field) {
            if ($link->{$field}) {
                $params[$field] = $link->{$field};
            }
        }

        $destination = $link->landing_url ?: route('register');
        $separator = str_contains($destination, '?') ? '&' : '?';

        return redirect()->to($destination . $separator . http_build_query($params));
    }
}
