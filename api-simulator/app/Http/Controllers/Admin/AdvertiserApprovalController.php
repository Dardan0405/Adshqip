<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityQuestion;
use App\Models\User;
use App\Support\AdminEventNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdvertiserApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->where('status', 'pending_verification')
            ->with(['userProfile', 'securityQuestion']);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('userProfile', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $pendingAdvertisers = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totalPending = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->where('status', 'pending_verification')
            ->count();

        $approvedToday = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->whereDate('updated_at', today())
            ->count();

        $rejectedCount = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->where('status', 'closed')
            ->count();

        $securityQuestions = SecurityQuestion::where('is_active', true)->get();

        return view('admin.advertiser-approvals.index', compact(
            'pendingAdvertisers',
            'totalPending',
            'approvedToday',
            'rejectedCount',
            'securityQuestions'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'email'                => 'required|email|unique:aq_users,email',
            'password'             => 'required|string|min:6|confirmed',
            'security_question_id' => 'required|exists:aq_security_questions,id',
            'security_answer'      => 'required|string|max:255',
        ]);

        $user = User::create([
            'email'                => $request->email,
            'password_hash'        => Hash::make($request->password),
            'role'                 => 'advertiser',
            'status'               => 'pending_verification',
            'security_question_id' => $request->security_question_id,
            'security_answer_hash' => Hash::make(strtolower(trim($request->security_answer))),
        ]);

        $user->userProfile()->create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
        ]);

        app(AdminEventNotifier::class)->notifyAdmins(
            'New Advertiser Created',
            $user->email . ' was created by admin and is pending approval.',
            'system',
            route('admin.advertiser-approvals'),
        );

        return redirect()->route('admin.advertiser-approvals')->with('success', 'Advertiser created and pending approval.');
    }

    public function approve($id)
    {
        $user = User::where('role', 'advertiser')
            ->where('status', 'pending_verification')
            ->findOrFail($id);

        $user->update(['status' => 'active']);

        app(AdminEventNotifier::class)->notifyAdmins(
            'Advertiser Approved',
            $user->email . ' was approved by admin.',
            'success',
            route('admin.advertiser-approvals'),
        );

        return response()->json(['success' => true, 'message' => 'Advertiser approved successfully.']);
    }

    public function reject($id)
    {
        $user = User::where('role', 'advertiser')
            ->where('status', 'pending_verification')
            ->findOrFail($id);

        $user->update(['status' => 'closed']);

        app(AdminEventNotifier::class)->notifyAdmins(
            'Advertiser Rejected',
            $user->email . ' was rejected by admin.',
            'warning',
            route('admin.advertiser-approvals'),
        );

        return response()->json(['success' => true, 'message' => 'Advertiser rejected.']);
    }

    public function export(Request $request)
    {
        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->where('status', 'pending_verification')
            ->with(['userProfile', 'securityQuestion'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'advertiser_approvals_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($advertisers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Security Question', 'Status', 'Created']);

            foreach ($advertisers as $adv) {
                $name = trim(($adv->userProfile->first_name ?? '') . ' ' . ($adv->userProfile->last_name ?? ''));
                fputcsv($file, [
                    $adv->id,
                    $name ?: 'Unknown',
                    $adv->email,
                    $adv->securityQuestion->question ?? 'N/A',
                    ucfirst($adv->status),
                    $adv->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
