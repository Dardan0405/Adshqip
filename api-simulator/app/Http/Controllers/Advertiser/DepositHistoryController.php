<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Support\AdvertiserPaymentManager;
use Illuminate\Http\Request;

class DepositHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $paymentManager = app(AdvertiserPaymentManager::class);

        $query = Transaction::query()
            ->with(['user.userProfile', 'paymentMethod'])
            ->where('user_id', $request->user()->id)
            ->where('type', 'deposit');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_type'])) {
            $paymentType = $paymentManager->normalizePaymentType($filters['payment_type']);
            $query->where('payment_gateway', $paymentManager->storageGateway($paymentType));
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $summaryQuery = clone $query;
        $totalAmount = (clone $summaryQuery)->sum('amount');
        $completedCount = (clone $summaryQuery)->where('status', 'completed')->count();
        $pendingCount = (clone $summaryQuery)->where('status', 'pending')->count();

        $deposits = $query
            ->orderByRaw('COALESCE(completed_at, created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        $deposits->getCollection()->transform(function (Transaction $deposit) use ($paymentManager) {
            $deposit->payment_type_label = $paymentManager->paymentTypeLabel($deposit->payment_gateway);

            return $deposit;
        });

        return view('advertiser.deposit-history.index', [
            'deposits' => $deposits,
            'paymentTypes' => $paymentManager->paymentTypeOptions(),
            'statuses' => [
                'completed' => 'Completed',
                'pending' => 'Pending',
                'failed' => 'Failed',
                'reversed' => 'Reversed',
            ],
            'totalAmount' => $totalAmount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'filters' => [
                'status' => $filters['status'] ?? '',
                'payment_type' => $filters['payment_type'] ?? '',
                'start_date' => $filters['start_date'] ?? '',
                'end_date' => $filters['end_date'] ?? '',
            ],
        ]);
    }
}
