<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqFeedController extends Controller
{
    public function index()
    {
        $language = request('lang', 'en');

        $faqs = Faq::query()
            ->published()
            ->byLanguage($language)
            ->ordered()
            ->get()
            ->map(function (Faq $faq) {
                return [
                    'id' => $faq->id,
                    'category' => $faq->category,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ];
            })
            ->groupBy('category')
            ->map(function ($items, $category) {
                return [
                    'category' => $category,
                    'items' => $items->values(),
                ];
            })
            ->values();

        return response()
            ->json([
                'faqs' => $faqs,
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
