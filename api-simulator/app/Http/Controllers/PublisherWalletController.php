<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payout;
use App\Models\StatDaily;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublisherWalletController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $user = $request->user()->load('profile');
        $userId = (int) $user->id;
        $activity = $this->walletActivity($userId, $filters);

        return view('publisher.wallet.index', [
            'user' => $user,
            'profile' => $user->profile,
            'summary' => $this->summary($userId),
            'activity' => $this->paginate($activity, $request),
            'recentInvoices' => $this->recentInvoices($userId),
            'recentPayouts' => $this->recentPayouts($userId),
            'methodLabels' => $this->methodLabels(),
            'filters' => $filters,
            'defaults' => [
                'start_date' => now()->subDays(30)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $rows = $this->walletActivity((int) $request->user()->id, $filters);
        $filename = 'publisher_wallet_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Reference', 'Status', 'Description', 'Credit', 'Debit', 'Balance Impact']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['type_label'],
                    $row['reference'],
                    $row['status'],
                    $row['description'],
                    number_format((float) $row['credit'], 4, '.', ''),
                    number_format((float) $row['debit'], 4, '.', ''),
                    number_format((float) $row['net'], 4, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'in:earning,payout,invoice'],
            'status' => ['nullable', 'string', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    private function summary(int $userId): array
    {
        $earned = (float) StatDaily::where('publisher_id', $userId)->sum('publisher_earnings');
        $pending = (float) Payout::where('user_id', $userId)->whereIn('status', ['pending', 'processing'])->sum('amount');
        $paid = (float) Payout::where('user_id', $userId)->where('status', 'completed')->sum('amount');
        $failed = (float) Payout::where('user_id', $userId)->whereIn('status', ['failed', 'cancelled'])->sum('amount');
        $invoiced = (float) Invoice::where('user_id', $userId)->where('type', 'publisher_payout')->sum('total_amount');
        $thisMonth = (float) StatDaily::where('publisher_id', $userId)
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->toDateString()])
            ->sum('publisher_earnings');

        $committed = $pending + $paid;

        return [
            'earned' => $earned,
            'pending' => $pending,
            'paid' => $paid,
            'failed' => $failed,
            'invoiced' => $invoiced,
            'this_month' => $thisMonth,
            'available' => max(0.0, $earned - $committed),
            'committed' => $committed,
        ];
    }

    private function walletActivity(int $userId, array $filters): Collection
    {
        $startDate = $filters['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();
        $search = strtolower(trim((string) ($filters['search'] ?? '')));
        $type = $filters['type'] ?? null;
        $status = $filters['status'] ?? null;

        $rows = collect();

        if ($type === null || $type === 'earning') {
            StatDaily::where('publisher_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw('date, SUM(publisher_earnings) as amount, SUM(impressions) as impressions, SUM(clicks) as clicks')
                ->groupBy('date')
                ->havingRaw('SUM(publisher_earnings) <> 0')
                ->get()
                ->each(function ($row) use ($rows) {
                    $amount = (float) $row->amount;
                    $rows->push([
                        'date' => $row->date?->format('Y-m-d') ?? (string) $row->date,
                        'sort_date' => $row->date?->format('Y-m-d') ?? (string) $row->date,
                        'type' => 'earning',
                        'type_label' => 'Publisher Earnings',
                        'reference' => 'EARN-' . ($row->date?->format('Ymd') ?? str_replace('-', '', (string) $row->date)),
                        'status' => 'earned',
                        'description' => number_format((int) $row->impressions) . ' impressions, ' . number_format((int) $row->clicks) . ' clicks',
                        'credit' => $amount,
                        'debit' => 0.0,
                        'net' => $amount,
                    ]);
                });
        }

        if ($type === null || $type === 'payout') {
            Payout::where('user_id', $userId)
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->orderByDesc('created_at')
                ->get()
                ->each(function (Payout $payout) use ($rows) {
                    $amount = (float) $payout->amount;
                    $isReversed = in_array($payout->status, ['failed', 'cancelled'], true);
                    $rows->push([
                        'date' => $payout->created_at?->format('Y-m-d H:i') ?? '',
                        'sort_date' => $payout->created_at?->format('Y-m-d H:i:s') ?? '',
                        'type' => 'payout',
                        'type_label' => 'Payout Request',
                        'reference' => $payout->payment_reference ?: ('PAYOUT-' . $payout->id),
                        'status' => $payout->status,
                        'description' => 'Payout via ' . $payout->payment_method_label,
                        'credit' => 0.0,
                        'debit' => $isReversed ? 0.0 : $amount,
                        'net' => $isReversed ? 0.0 : -$amount,
                    ]);
                });
        }

        if ($type === null || $type === 'invoice') {
            Invoice::where('user_id', $userId)
                ->where('type', 'publisher_payout')
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->orderByDesc('created_at')
                ->get()
                ->each(function (Invoice $invoice) use ($rows) {
                    $amount = (float) $invoice->total_amount;
                    $rows->push([
                        'date' => $invoice->created_at?->format('Y-m-d H:i') ?? '',
                        'sort_date' => $invoice->created_at?->format('Y-m-d H:i:s') ?? '',
                        'type' => 'invoice',
                        'type_label' => 'Payout Invoice',
                        'reference' => $invoice->invoice_number,
                        'status' => $invoice->status,
                        'description' => 'Invoice total ' . $invoice->currency . ' ' . number_format($amount, 2),
                        'credit' => 0.0,
                        'debit' => 0.0,
                        'net' => 0.0,
                    ]);
                });
        }

        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search) {
                return str_contains(strtolower($row['type_label'] . ' ' . $row['reference'] . ' ' . $row['status'] . ' ' . $row['description']), $search);
            });
        }

        if ($status && $type === null) {
            $rows = $rows->filter(fn (array $row) => $row['status'] === $status);
        }

        return $rows->sortByDesc('sort_date')->values();
    }

    private function recentInvoices(int $userId): Collection
    {
        return Invoice::where('user_id', $userId)
            ->where('type', 'publisher_payout')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    private function recentPayouts(int $userId): Collection
    {
        return Payout::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    private function paginate(Collection $rows, Request $request): LengthAwarePaginator
    {
        $perPage = 20;
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $rows->slice(($page - 1) * $perPage, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function methodLabels(): array
    {
        return [
            'bankwire' => 'Bank Wire',
            'paypal' => 'PayPal',
            'bitcoin' => 'Bitcoin',
            'stripe' => 'Stripe',
            'authorize_net' => 'Authorize.net',
            'wire_transfer' => 'Wire Transfer',
            'crypto' => 'Crypto',
            'payoneer' => 'Payoneer',
        ];
    }
}
