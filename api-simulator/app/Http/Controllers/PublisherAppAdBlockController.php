<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ZoneServeController;
use App\Models\PlatformSetting;
use App\Models\TelegramMiniApp;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublisherAppAdBlockController extends Controller
{
    protected function buildServeUrl(Zone $zone): string
    {
        $token = ZoneServeController::encodeToken($zone->id);
        $serveDomain = PlatformSetting::getServeDomain();
        $servePath = PlatformSetting::getServePath();

        return "{$serveDomain}{$servePath}/{$token}.js";
    }

    protected function generateInvocationCodes(Zone $zone): array
    {
        $serveUrl = $this->buildServeUrl($zone);
        $sizeStyle = '';

        if ($zone->size_key && preg_match('/^(\d+)x(\d+)$/', $zone->size_key, $m)) {
            $sizeStyle = " style=\"width:{$m[1]}px;height:{$m[2]}px;\"";
        }

        $videoBase = '<div class="adshqip-video-player" data-zone-id="' . $zone->id . '" data-player="%s"></div>' . "\n"
            . '<script async src="' . e($serveUrl) . '"></script>';

        return [
            'js' => '<div id="adshqip-zone-' . $zone->id . '" data-zone-id="' . $zone->id . '" data-format="' . e($zone->format_key) . '" data-size="' . e($zone->size_key) . '"></div>' . "\n" . '<script async src="' . e($serveUrl) . '"></script>',
            'iframe' => '<iframe src="' . e($serveUrl) . '" loading="lazy" frameborder="0" scrolling="no"' . $sizeStyle . '></iframe>',
            'inline' => '<div class="adshqip-inline-video" data-zone-id="' . $zone->id . '"></div>' . "\n" . '<script async src="' . e($serveUrl) . '"></script>',
            'real' => sprintf($videoBase, 'real'),
            'small' => sprintf($videoBase, 'small'),
            'box' => sprintf($videoBase, 'box'),
            'head' => sprintf($videoBase, 'head'),
            'overlay' => '<div class="adshqip-overlay" data-zone-id="' . $zone->id . '"></div>' . "\n" . '<script async src="' . e($serveUrl) . '"></script>',
            'curl' => "<?php\n\$ch = curl_init('" . addslashes($serveUrl) . "');\ncurl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);\necho curl_exec(\$ch);\ncurl_close(\$ch);\n",
        ];
    }

    public function store(Request $request, int $appId): JsonResponse
    {
        $app = TelegramMiniApp::query()
            ->where('user_id', $request->user()->id)
            ->where('is_deleted', false)
            ->findOrFail($appId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'format_id' => 'required|string|max:50',
            'size_id' => 'nullable|string|max:50',
            'zone_type' => 'required|string|max:50',
            'placement' => 'required|in:header,sidebar,content,footer,overlay,interstitial,push',
            'floor_price' => 'nullable|numeric|min:0',
            'passback' => 'nullable|string',
            'image_width' => 'nullable|integer|min:1',
            'image_height' => 'nullable|integer|min:1',
            'html_template' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'bg_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'sponsored_prefix' => 'nullable|string|max:255',
            'css_path' => 'nullable|string|max:255',
            'inline_video' => 'nullable|boolean',
        ]);

        $zone = Zone::query()->create([
            'site_id' => null,
            'choose_type' => 'app',
            'mobile_app_id' => $app->id,
            'name' => $validated['name'],
            'format_id' => null,
            'format_key' => $validated['format_id'],
            'size_id' => null,
            'size_key' => $validated['size_id'] ?? null,
            'zone_type' => $validated['zone_type'],
            'placement' => $validated['placement'],
            'floor_price' => (float) ($validated['floor_price'] ?? 0),
            'passback' => $validated['passback'] ?? null,
            'image_width' => $validated['image_width'] ?? null,
            'image_height' => $validated['image_height'] ?? null,
            'html_template' => $validated['html_template'] ?? null,
            'custom_css' => $validated['custom_css'] ?? null,
            'bg_color' => $validated['bg_color'] ?? null,
            'sponsored_prefix' => $validated['sponsored_prefix'] ?? null,
            'css_path' => $validated['css_path'] ?? null,
            'inline_video' => (bool) ($validated['inline_video'] ?? false),
            'status' => 'active',
            'is_deleted' => false,
        ]);

        $adCode = $this->generateInvocationCodes($zone)['js'];
        $zone->update(['ad_code' => $adCode]);

        return response()->json([
            'success' => true,
            'zone_id' => $zone->id,
            'ad_code' => $adCode,
            'codes' => $this->generateInvocationCodes($zone),
            'message' => 'App adblock created successfully.',
        ]);
    }
}
