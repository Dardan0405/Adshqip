<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterSubscriptionController extends Controller
{
    public function options()
    {
        return response('', 204)->withHeaders($this->corsHeaders());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'source' => ['nullable', 'in:landing_page,dashboard,api,import'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)->withHeaders($this->corsHeaders());
        }

        $newsletter = Newsletter::firstOrNew(['email' => strtolower(trim((string) $request->input('email')))]);
        $newsletter->source = $request->input('source', 'landing_page');
        $newsletter->status = 'subscribed';
        $newsletter->subscribed_at = now();
        $newsletter->unsubscribed_at = null;
        $newsletter->save();

        return response()->json([
            'success' => true,
            'message' => 'Thanks for subscribing to the Adshqip newsletter.',
        ])->withHeaders($this->corsHeaders());
    }

    private function corsHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Accept, Origin',
        ];
    }
}
