<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramMiniApp;
use Illuminate\Http\Request;

class MobileApplicationApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $statsQuery = TelegramMiniApp::query()
            ->join('aq_users', 'aq_telegram_mini_apps.user_id', '=', 'aq_users.id')
            ->where('aq_telegram_mini_apps.is_deleted', false)
            ->where('aq_users.is_deleted', false);

        $totalApplications = (clone $statsQuery)->count();
        $pendingCount = (clone $statsQuery)->where('aq_telegram_mini_apps.status', 'pending_review')->count();
        $approvedCount = (clone $statsQuery)->where('aq_telegram_mini_apps.status', 'active')->count();
        $rejectedCount = (clone $statsQuery)->where('aq_telegram_mini_apps.status', 'suspended')->count();

        $applications = $this->buildBaseQuery($request)
            ->with('owner')
            ->orderByDesc('aq_telegram_mini_apps.created_at')
            ->select('aq_telegram_mini_apps.*')
            ->paginate(20)
            ->withQueryString();

        return view('admin.mobile-application-approvals.index', compact(
            'applications',
            'totalApplications',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function approve($id)
    {
        $application = TelegramMiniApp::query()
            ->where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $application->update([
            'status' => 'active',
            'admin_approved' => true,
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mobile application approved successfully.',
        ]);
    }

    public function reject($id)
    {
        $application = TelegramMiniApp::query()
            ->where('is_deleted', false)
            ->where('status', 'pending_review')
            ->findOrFail($id);

        $application->update([
            'status' => 'suspended',
            'admin_approved' => false,
            'rejection_reason' => 'Rejected by admin approval flow.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mobile application rejected successfully.',
        ]);
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = TelegramMiniApp::query()
            ->join('aq_users', 'aq_telegram_mini_apps.user_id', '=', 'aq_users.id')
            ->where('aq_telegram_mini_apps.is_deleted', false)
            ->where('aq_users.is_deleted', false)
            ->where('aq_telegram_mini_apps.status', 'pending_review');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn ($q) => $q->orWhere('aq_telegram_mini_apps.id', (int) $search))
                    ->orWhere('aq_telegram_mini_apps.app_name', 'like', '%' . $search . '%')
                    ->orWhere('aq_telegram_mini_apps.app_url', 'like', '%' . $search . '%')
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }
}
