<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityQuestion;
use App\Models\User;
use App\Support\AdminEventNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PublisherApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'publisher')
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

        $pendingPublishers = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totalPending = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->where('status', 'pending_verification')
            ->count();

        $approvedToday = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->whereDate('updated_at', today())
            ->count();

        $rejectedCount = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->where('status', 'closed')
            ->count();

        $securityQuestions = SecurityQuestion::where('is_active', true)->get();

        return view('admin.publisher-approvals.index', compact(
            'pendingPublishers',
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
            'website_url'          => 'nullable|url|max:500',
            'security_question_id' => 'required|exists:aq_security_questions,id',
            'security_answer'      => 'required|string|max:255',
        ]);

        $user = User::create([
            'email'                => $request->email,
            'password_hash'        => Hash::make($request->password),
            'role'                 => 'publisher',
            'status'               => 'pending_verification',
            'security_question_id' => $request->security_question_id,
            'security_answer_hash' => Hash::make(strtolower(trim($request->security_answer))),
        ]);

        $user->userProfile()->create([
            'first_name'  => $request->first_name,
            'last_name'   => $request->last_name,
            'website_url' => $request->website_url,
        ]);

        app(AdminEventNotifier::class)->notifyAdmins(
            'New Publisher Created',
            $user->email . ' was created by admin and is pending approval.',
            'system',
            route('admin.publisher-approvals'),
        );

        return redirect()->route('admin.publisher-approvals')->with('success', 'Publisher created and pending approval.');
    }

    public function approve($id)
    {
        $user = User::where('role', 'publisher')
            ->where('status', 'pending_verification')
            ->findOrFail($id);

        $user->update(['status' => 'active']);

        app(AdminEventNotifier::class)->notifyAdmins(
            'Publisher Approved',
            $user->email . ' was approved by admin.',
            'success',
            route('admin.publisher-approvals'),
        );

        return response()->json(['success' => true, 'message' => 'Publisher approved successfully.']);
    }

    public function reject($id)
    {
        $user = User::where('role', 'publisher')
            ->where('status', 'pending_verification')
            ->findOrFail($id);

        $user->update(['status' => 'closed']);

        app(AdminEventNotifier::class)->notifyAdmins(
            'Publisher Rejected',
            $user->email . ' was rejected by admin.',
            'warning',
            route('admin.publisher-approvals'),
        );

        return response()->json(['success' => true, 'message' => 'Publisher rejected.']);
    }

    public function export(Request $request)
    {
        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->where('status', 'pending_verification')
            ->with(['userProfile', 'securityQuestion'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'publisher_approvals_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($publishers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Website', 'Security Question', 'Status', 'Created']);

            foreach ($publishers as $pub) {
                $name = trim(($pub->userProfile->first_name ?? '') . ' ' . ($pub->userProfile->last_name ?? ''));
                fputcsv($file, [
                    $pub->id,
                    $name ?: 'Unknown',
                    $pub->email,
                    $pub->userProfile->website_url ?? 'N/A',
                    $pub->securityQuestion->question ?? 'N/A',
                    ucfirst($pub->status),
                    $pub->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
