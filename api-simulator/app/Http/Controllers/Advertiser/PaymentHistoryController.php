<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\StatDaily;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'start_month' => ['nullable', 'date_format:Y-m'],
            'end_month' => ['nullable', 'date_format:Y-m', 'after_or_equal:start_month'],
        ]);

        $advertiserId = auth()->id();
        $startDate = isset($filters['start_month'])
            ? Carbon::createFromFormat('Y-m', $filters['start_month'])->startOfMonth()
            : null;
        $endDate = isset($filters['end_month'])
            ? Carbon::createFromFormat('Y-m', $filters['end_month'])->endOfMonth()
            : null;

        $depositDays = $this->depositDays($advertiserId, $startDate, $endDate);
        $spendDays = $this->spendDays($advertiserId, $startDate, $endDate);

        $months = $this->buildMonths($depositDays, $spendDays);
        $summary = [
            'total_deposits' => $months->sum('total_deposits'),
            'total_spend' => $months->sum('total_spend'),
            'net_balance' => $months->sum('total_deposits') - $months->sum('total_spend'),
            'current_month_deposits' => $months->where('month', now()->format('Y-m'))->sum('total_deposits'),
        ];

        $payments = $this->paginate($months, $request);

        return view('advertiser.payment-history.index', [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => [
                'start_month' => $filters['start_month'] ?? '',
                'end_month' => $filters['end_month'] ?? '',
            ],
        ]);
    }

    private function depositDays(int $advertiserId, ?Carbon $startDate, ?Carbon $endDate)
    {
        $query = Transaction::query()
            ->where('user_id', $advertiserId)
            ->where('type', 'deposit')
            ->where('status', 'completed')
            ->whereNotNull('completed_at');

        if ($startDate) {
            $query->whereDate('completed_at', '>=', $startDate->toDateString());
        }

        if ($endDate) {
            $query->whereDate('completed_at', '<=', $endDate->toDateString());
        }

        return $query
            ->get(['amount', 'completed_at'])
            ->groupBy(fn (Transaction $transaction) => $transaction->completed_at->toDateString())
            ->map(fn ($rows, $date) => (object) [
                'date' => $date,
                'paid' => (float) $rows->sum('amount'),
            ]);
    }

    private function spendDays(int $advertiserId, ?Carbon $startDate, ?Carbon $endDate)
    {
        $query = StatDaily::query()
            ->where('advertiser_id', $advertiserId);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate->toDateString());
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate->toDateString());
        }

        return $query
            ->selectRaw('date, COALESCE(SUM(revenue), 0) as spend')
            ->groupBy('date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString())
            ->map(fn ($row, $date) => (object) [
                'date' => $date,
                'spend' => (float) $row->spend,
            ]);
    }

    private function buildMonths($depositDays, $spendDays)
    {
        return $depositDays
            ->keys()
            ->merge($spendDays->keys())
            ->unique()
            ->sortDesc()
            ->groupBy(fn ($date) => Carbon::parse($date)->format('Y-m'))
            ->map(function ($dates, $month) use ($depositDays, $spendDays) {
                $details = $dates
                    ->sortDesc()
                    ->map(function ($date) use ($depositDays, $spendDays) {
                        $carbonDate = Carbon::parse($date);

                        return (object) [
                            'date' => $date,
                            'date_formatted' => $carbonDate->format('M d, Y'),
                            'paid' => (float) optional($depositDays->get($date))->paid,
                            'spend' => (float) optional($spendDays->get($date))->spend,
                        ];
                    })
                    ->values();

                return (object) [
                    'id' => str_replace('-', '', $month),
                    'month' => $month,
                    'month_formatted' => Carbon::parse($month . '-01')->format('F Y'),
                    'total_deposits' => $details->sum('paid'),
                    'total_spend' => $details->sum('spend'),
                    'details' => $details,
                ];
            })
            ->sortByDesc('month')
            ->values();
    }

    private function paginate($months, Request $request): LengthAwarePaginator
    {
        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $months->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $months->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
