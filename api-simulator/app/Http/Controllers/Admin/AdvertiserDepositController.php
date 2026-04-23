<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserDeposit;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Support\AdvertiserNotificationManager;
use App\Support\AdvertiserPaymentGateway;
use App\Support\AdvertiserPaymentManager;
use Illuminate\Http\Request;

class AdvertiserDepositController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'advertiser_id' => ['nullable', 'integer'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalAmount = (clone $query)->sum('aq_transactions.amount');
        $completedCount = (clone $query)->where('aq_transactions.status', 'completed')->count();
        $pendingCount = (clone $query)->where('aq_transactions.status', 'pending')->count();
        $thisMonthAmount = (clone $query)
            ->whereBetween('aq_transactions.completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('aq_transactions.amount');

        $deposits = $query
            ->with(['user.userProfile', 'paymentMethod'])
            ->orderByRaw('COALESCE(aq_transactions.completed_at, aq_transactions.created_at) DESC')
            ->select('aq_transactions.*')
            ->paginate(20)
            ->withQueryString();

        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $paymentTypes = PlatformSetting::getAdvertiserPaymentTypes();

        $statuses = [
            'completed' => 'Completed',
            'pending' => 'Pending',
            'failed' => 'Failed',
            'reversed' => 'Reversed',
        ];

        return view('admin.advertiser-deposits.index', compact(
            'deposits',
            'advertisers',
            'paymentTypes',
            'statuses',
            'totalAmount',
            'completedCount',
            'pendingCount',
            'thisMonthAmount'
        ));
    }

    public function export(Request $request)
    {
        $deposits = $this->buildBaseQuery($request)
            ->with(['user.userProfile', 'paymentMethod'])
            ->orderByRaw('COALESCE(aq_transactions.completed_at, aq_transactions.created_at) DESC')
            ->select('aq_transactions.*')
            ->get();

        $filename = 'advertiser_deposits_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($deposits) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Paid Date', 'Name', 'Email', 'Amount', 'Payment Type', 'Status']);

            foreach ($deposits as $deposit) {
                $name = trim(($deposit->user->userProfile->first_name ?? '') . ' ' . ($deposit->user->userProfile->last_name ?? ''));

                fputcsv($file, [
                    $deposit->id,
                    optional($deposit->completed_at)->format('Y-m-d H:i:s') ?: '',
                    $name ?: 'Unknown',
                    $deposit->user->email ?? 'N/A',
                    number_format((float) $deposit->amount, 2, '.', ''),
                    $deposit->payment_type_label,
                    ucfirst($deposit->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function complete(Request $request, AdvertiserDeposit $deposit)
    {
        abort_unless($deposit->type === 'deposit', 404);
        abort_unless(optional($deposit->user)->role === 'advertiser', 404);

        if ($deposit->status === 'completed') {
            return redirect()
                ->to($this->depositRedirectUrl($request))
                ->with('success', 'Deposit #' . $deposit->id . ' is already completed.');
        }

        $transaction = Transaction::findOrFail($deposit->id);
        app(AdvertiserPaymentGateway::class)->complete(
            $transaction,
            $transaction->gateway_txn_id ?: 'admin-wire-' . $transaction->id,
            [
                'provider' => 'bank_wire',
                'completed_by_admin_id' => $request->user()?->id,
                'completed_from' => 'admin_advertiser_deposits',
            ]
        );

        app(AdvertiserNotificationManager::class)->deliver(
            $deposit->user->fresh('profile'),
            'payment_approved',
            'Payment Approved',
            'Your deposit #' . $deposit->id . ' has been approved and completed.',
            route('advertiser.payments.history'),
            $request->user()?->id,
            true
        );

        return redirect()
            ->to($this->depositRedirectUrl($request))
            ->with('success', 'Deposit #' . $deposit->id . ' completed and advertiser balance updated.');
    }

    public function reject(Request $request, AdvertiserDeposit $deposit)
    {
        abort_unless($deposit->type === 'deposit', 404);
        abort_unless(optional($deposit->user)->role === 'advertiser', 404);

        if ($deposit->status !== 'pending') {
            return redirect()
                ->to($this->depositRedirectUrl($request))
                ->with('success', 'Deposit #' . $deposit->id . ' is no longer pending.');
        }

        $deposit->forceFill([
            'status' => 'failed',
            'gateway_status' => 'failed',
            'admin_note' => trim((string) $deposit->admin_note . ' Admin rejected this deposit.'),
        ])->save();

        app(AdvertiserNotificationManager::class)->deliver(
            $deposit->user->fresh('profile'),
            'payment_rejected',
            'Payment Rejected',
            'Your deposit #' . $deposit->id . ' has been rejected by admin.',
            route('advertiser.payments.history'),
            $request->user()?->id,
            true
        );

        return redirect()
            ->to($this->depositRedirectUrl($request))
            ->with('success', 'Deposit #' . $deposit->id . ' rejected.');
    }

    private function depositRedirectUrl(Request $request): string
    {
        return $request->input('redirect_to') === 'approvals'
            ? route('admin.advertiser-payment-approvals')
            : route('admin.advertiser-deposits');
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = AdvertiserDeposit::query()
            ->deposits()
            ->forAdvertisers();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), function ($numericQuery) use ($search) {
                        $numericQuery->orWhere('aq_transactions.id', (int) $search);
                    })
                    ->orWhere('aq_transactions.gateway_txn_id', 'like', '%' . $search . '%')
                    ->orWhere('aq_transactions.description', 'like', '%' . $search . '%')
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

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('aq_transactions.user_id', $advertiserId);
        }

        if ($paymentType = $request->get('payment_type')) {
            $normalized = app(AdvertiserPaymentManager::class)->normalizePaymentType($paymentType);
            $legacyMap = [
                PlatformSetting::ADVERTISER_PAYMENT_STRIPE => ['stripe', 'credit_card'],
                PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => ['authorize'],
                PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => ['bitcoin', 'crypto', 'coinbase'],
                PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER => ['wire_transfer', 'manual'],
                PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => ['paypal'],
            ];

            $query->whereIn('aq_transactions.payment_gateway', $legacyMap[$normalized] ?? [$normalized]);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_transactions.status', $status);
        }

        $query->paidBetween($request->get('start_date'), $request->get('end_date'));

        return $query;
    }
}
