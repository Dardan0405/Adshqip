<?php

namespace App\Http\Controllers;

use App\Models\ApiDoc;

class ApiDocFeedController extends Controller
{
    public function index()
    {
        $docs = ApiDoc::query()
            ->published()
            ->ordered()
            ->get()
            ->map(fn (ApiDoc $doc) => [
                'id' => $doc->id,
                'slug' => $doc->slug,
                'title' => $doc->title,
                'category' => $doc->category,
                'http_method' => $doc->http_method,
                'endpoint_path' => $doc->endpoint_path,
                'auth_required' => (bool) $doc->auth_required,
                'required_permission' => $doc->required_permission,
                'description' => $doc->description,
                'headers_example' => $doc->headers_example,
                'request_example' => $doc->request_example,
                'response_example' => $doc->response_example,
                'notes' => $doc->notes,
            ])
            ->values();

        return response()
            ->json(['api_docs' => $docs])
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
