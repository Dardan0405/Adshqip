<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserDeposit;
use App\Models\Payout;
use App\Models\User;
use App\Support\AdvertiserNotificationManager;
use Illuminate\Http\Request;

class AdvertiserPaymentApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalAmount = (clone $query)->sum('aq_payouts.amount');
        $completedCount = (clone $query)->where('aq_payouts.status', 'completed')->count();
        $pendingCount = (clone $query)->where('aq_payouts.status', 'pending')->count();
        $thisMonthAmount = (clone $query)
            ->whereBetween('aq_payouts.processed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('aq_payouts.amount');

        $payouts = $query
            ->with(['user.userProfile'])
            ->orderByDesc('aq_payouts.created_at')
            ->select('aq_payouts.*')
            ->paginate(20)
            ->withQueryString();

        $pendingDeposits = AdvertiserDeposit::query()
            ->deposits()
            ->forAdvertisers()
            ->where('aq_transactions.status', 'pending')
            ->whereIn('aq_transactions.payment_gateway', ['wire_transfer', 'manual'])
            ->with(['user.userProfile'])
            ->orderByDesc('aq_transactions.created_at')
            ->select('aq_transactions.*')
            ->get();

        $users = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $paymentMethods = [
            'paypal' => 'PayPal',
            'wire_transfer' => 'Wire Transfer',
            'crypto' => 'Crypto',
            'payoneer' => 'Payoneer',
        ];

        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];

        return view('admin.advertiser-payment-approvals.index', compact(
            'payouts',
            'pendingDeposits',
            'users',
            'paymentMethods',
            'statuses',
            'totalAmount',
            'completedCount',
            'pendingCount',
            'thisMonthAmount'
        ));
    }

    public function show($id)
    {
        $payout = Payout::with(['user.userProfile'])
            ->whereHas('user', fn($q) => $q->where('role', 'advertiser'))
            ->findOrFail($id);

        $name = trim(($payout->user->userProfile->first_name ?? '') . ' ' . ($payout->user->userProfile->last_name ?? '')) ?: 'Unknown';

        return response()->json([
            'id' => $payout->id,
            'name' => $name,
            'email' => $payout->user->email,
            'role' => $payout->user->role,
            'amount' => round((float) $payout->amount, 2),
            'currency' => $payout->currency,
            'payment_method' => $payout->payment_method_label,
            'payment_reference' => $payout->payment_reference,
            'status' => ucfirst($payout->status),
            'period_start' => $payout->period_start?->format('M d, Y'),
            'period_end' => $payout->period_end?->format('M d, Y'),
            'notes' => $payout->notes,
            'processed_at' => $payout->processed_at?->format('M d, Y H:i'),
            'created_at' => $payout->created_at?->format('M d, Y H:i'),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $payout = Payout::where('status', 'pending')
            ->whereHas('user', fn($q) => $q->where('role', 'advertiser'))
            ->findOrFail($id);

        $payout->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        app(AdvertiserNotificationManager::class)->deliver(
            $payout->user->fresh('profile'),
            'payment_approved',
            'Payment Approved',
            'Your advertiser payment request #' . $payout->id . ' has been approved.',
            route('advertiser.payments.history'),
            $request->user()?->id,
            true
        );

        return response()->json(['success' => true, 'message' => 'Advertiser payment approved and marked as completed.']);
    }

    public function reject(Request $request, $id)
    {
        $payout = Payout::where('status', 'pending')
            ->whereHas('user', fn($q) => $q->where('role', 'advertiser'))
            ->findOrFail($id);

        $payout->update([
            'status' => 'cancelled',
            'processed_at' => now(),
        ]);

        app(AdvertiserNotificationManager::class)->deliver(
            $payout->user->fresh('profile'),
            'payment_rejected',
            'Payment Rejected',
            'Your advertiser payment request #' . $payout->id . ' has been rejected.',
            route('advertiser.payments.history'),
            $request->user()?->id,
            true
        );

        return response()->json(['success' => true, 'message' => 'Advertiser payment rejected.']);
    }

    public function export(Request $request)
    {
        $payouts = $this->buildBaseQuery($request)
            ->with(['user.userProfile'])
            ->orderByDesc('aq_payouts.created_at')
            ->select('aq_payouts.*')
            ->get();

        $filename = 'advertiser_payment_approvals_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payouts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Paid Date', 'Name', 'Email', 'Amount', 'Payment Method', 'Status']);

            foreach ($payouts as $payout) {
                $name = trim(($payout->user->userProfile->first_name ?? '') . ' ' . ($payout->user->userProfile->last_name ?? ''));
                fputcsv($file, [
                    $payout->id,
                    $payout->processed_at?->format('Y-m-d H:i:s') ?: '',
                    $name ?: 'Unknown',
                    $payout->user->email ?? 'N/A',
                    number_format((float) $payout->amount, 2, '.', ''),
                    $payout->payment_method_label,
                    ucfirst($payout->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = Payout::query()
            ->join('aq_users', 'aq_payouts.user_id', '=', 'aq_users.id')
            ->where('aq_users.is_deleted', false)
            ->where('aq_users.role', 'advertiser');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), fn($q) => $q->orWhere('aq_payouts.id', (int) $search))
                    ->orWhere('aq_payouts.payment_reference', 'like', '%' . $search . '%')
                    ->orWhere('aq_users.email', 'like', '%' . $search . '%')
                    ->orWhereExists(function ($profileQuery) use ($search) {
                        $profileQuery
                            ->selectRaw('1')
                            ->from('aq_user_profiles')
                            ->whereColumn('aq_user_profiles.user_id', 'aq_users.id')
                            ->where(function ($nameQuery) use ($search) {
                                $nameQuery
                                    ->where('aq_user_profiles.first_name', 'like', '%' . $search . '%')
                                    ->orWhere('aq_user_profiles.last_name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($userId = $request->get('user_id')) {
            $query->where('aq_payouts.user_id', $userId);
        }

        if ($paymentMethod = $request->get('payment_method')) {
            $query->where('aq_payouts.payment_method', $paymentMethod);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_payouts.status', $status);
        }

        $query->paidBetween($request->get('start_date'), $request->get('end_date'));

        return $query;
    }
}
