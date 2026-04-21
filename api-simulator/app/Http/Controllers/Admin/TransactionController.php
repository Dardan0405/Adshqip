<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'string', 'max:50'],
            'gateway' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $query = $this->buildBaseQuery($request);

        $totalAmount = (clone $query)->sum('aq_transactions.amount');
        $completedCount = (clone $query)->where('aq_transactions.status', 'completed')->count();
        $pendingCount = (clone $query)->where('aq_transactions.status', 'pending')->count();
        $thisMonthAmount = (clone $query)
            ->whereBetween('aq_transactions.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('aq_transactions.amount');

        $transactions = $query
            ->with(['user.userProfile', 'paymentMethod', 'campaign'])
            ->orderByRaw('COALESCE(aq_transactions.completed_at, aq_transactions.created_at) DESC')
            ->select('aq_transactions.*')
            ->paginate(20)
            ->withQueryString();

        $users = User::query()
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        $types = [
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'ad_spend' => 'Ad Spend',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            'welcome_bonus' => 'Welcome Bonus',
            'referral_credit' => 'Referral Credit',
        ];

        $gateways = [
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'coinbase' => 'Bitcoin',
            'wire_transfer' => 'Bank Wire',
            'authorize' => 'Authorize.net',
            'bitcoin' => 'Bitcoin',
            'manual' => 'Manual',
        ];

        $statuses = [
            'completed' => 'Completed',
            'pending' => 'Pending',
            'failed' => 'Failed',
            'reversed' => 'Reversed',
        ];

        return view('admin.transactions.index', compact(
            'transactions',
            'users',
            'types',
            'gateways',
            'statuses',
            'totalAmount',
            'completedCount',
            'pendingCount',
            'thisMonthAmount'
        ));
    }

    protected function buildBaseQuery(Request $request)
    {
        $query = Transaction::query()
            ->leftJoin('aq_users', 'aq_transactions.user_id', '=', 'aq_users.id');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($inner) use ($search) {
                $inner
                    ->when(is_numeric($search), function ($numericQuery) use ($search) {
                        $numericQuery->orWhere('aq_transactions.id', (int) $search);
                    })
                    ->orWhere('aq_transactions.gateway_txn_id', 'like', '%' . $search . '%')
                    ->orWhere('aq_transactions.description', 'like', '%' . $search . '%')
                    ->orWhere('aq_transactions.admin_note', 'like', '%' . $search . '%')
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
            $query->where('aq_transactions.user_id', $userId);
        }

        if ($type = $request->get('type')) {
            $query->where('aq_transactions.type', $type);
        }

        if ($gateway = $request->get('gateway')) {
            $query->where('aq_transactions.payment_gateway', $gateway);
        }

        if ($status = $request->get('status')) {
            $query->where('aq_transactions.status', $status);
        }

        $query->betweenDates($request->get('start_date'), $request->get('end_date'));

        return $query;
    }
}
