<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;

class TestimonialFeedController extends Controller
{
    public function index()
    {
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());

        $testimonials = Testimonial::query()
            ->published()
            ->ordered()
            ->get()
            ->map(function (Testimonial $testimonial) use ($baseUrl) {
                $avatarUrl = $testimonial->author_avatar_url;
                // Convert relative storage URLs to absolute URLs
                if ($avatarUrl && str_starts_with($avatarUrl, '/storage/')) {
                    $avatarUrl = $baseUrl . $avatarUrl;
                }

                return [
                    'id' => $testimonial->id,
                    'author_name' => $testimonial->author_name,
                    'author_title' => $testimonial->author_title,
                    'author_company' => $testimonial->author_company,
                    'author_avatar_url' => $avatarUrl,
                    'quote' => $testimonial->quote,
                    'rating' => $testimonial->rating,
                    'is_featured' => (bool) $testimonial->is_featured,
                ];
            })
            ->values();

        return response()
            ->json([
                'testimonials' => $testimonials,
            ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
    }

    public function options()
    {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
    }
}
