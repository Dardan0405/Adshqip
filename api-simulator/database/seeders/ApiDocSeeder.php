<?php

namespace Database\Seeders;

use App\Models\ApiDoc;
use Illuminate\Database\Seeder;

class ApiDocSeeder extends Seeder
{
    public function run(): void
    {
        $docs = [
            [
                'slug' => 'api-key-authentication',
                'title' => 'API Key Authentication',
                'category' => 'Authentication',
                'http_method' => 'GET',
                'endpoint_path' => '/api/integration/ping',
                'auth_required' => true,
                'required_permission' => null,
                'description' => 'Validates X-API-Key and X-API-Secret headers and returns the authenticated API key metadata.',
                'headers_example' => "X-API-Key: AK_OLKJPNIX1PS9ZYRSA1M2CQZP\nX-API-Secret: SK_your_secret_here\nAccept: application/json",
                'request_example' => "curl -H \"X-API-Key: AK_...\" -H \"X-API-Secret: SK_...\" http://127.0.0.1:8000/api/integration/ping",
                'response_example' => "{\n  \"ok\": true,\n  \"message\": \"API key authentication works.\"\n}",
                'notes' => 'The API secret is checked against the stored SHA-256 hash. Revoked or expired keys are rejected.',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'reports-permission-test',
                'title' => 'Reports Permission Test',
                'category' => 'Reports',
                'http_method' => 'GET',
                'endpoint_path' => '/api/integration/reports-test',
                'auth_required' => true,
                'required_permission' => 'read_reports',
                'description' => 'Protected test endpoint that requires a valid API key with the read_reports permission.',
                'headers_example' => "X-API-Key: AK_...\nX-API-Secret: SK_...\nAccept: application/json",
                'request_example' => "curl -H \"X-API-Key: AK_...\" -H \"X-API-Secret: SK_...\" http://127.0.0.1:8000/api/integration/reports-test",
                'response_example' => "{\n  \"ok\": true,\n  \"message\": \"read_reports permission accepted.\"\n}",
                'notes' => 'Use this endpoint to verify permission enforcement for generated API keys.',
                'is_published' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($docs as $doc) {
            ApiDoc::updateOrCreate(['slug' => $doc['slug']], $doc);
        }
    }
}
