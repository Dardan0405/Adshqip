<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublisherActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('publisher.activity-log', compact('logs'));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        ActivityLog::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        ActivityLog::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
