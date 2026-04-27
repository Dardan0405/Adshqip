<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payout;
use App\Models\StatDaily;
use App\Support\PublisherNotificationManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublisherPayoutController extends Controller
{
    private const METHOD_MAP = [
        'bankwire'      => 'wire_transfer',
        'paypal'        => 'paypal',
        'bitcoin'       => 'crypto',
        'stripe'        => 'wire_transfer',
        'authorize_net' => 'wire_transfer',
    ];

    public function __construct(private readonly PublisherNotificationManager $notifier) {}

    private function availableBalance(int $userId): float
    {
        $earned    = (float) StatDaily::where('publisher_id', $userId)->sum('publisher_earnings');
        $committed = (float) Payout::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->sum('amount');

        return max(0.0, $earned - $committed);
    }

    public function index(Request $request): View
    {
        $user   = $request->user()->load('profile');
        $userId = $user->id;

        $payouts = Payout::query()
            ->leftJoin('aq_invoices', 'aq_payouts.payment_reference', '=', 'aq_invoices.invoice_number')
            ->where('aq_payouts.user_id', $userId)
            ->select([
                'aq_payouts.id',
                'aq_payouts.amount',
                'aq_payouts.currency',
                'aq_payouts.payment_method',
                'aq_payouts.payment_reference',
                'aq_payouts.status as payout_status',
                'aq_payouts.period_start',
                'aq_payouts.period_end',
                'aq_payouts.created_at',
                'aq_invoices.id as invoice_id',
                'aq_invoices.invoice_number',
                'aq_invoices.total_amount as invoice_amount',
                'aq_invoices.status as invoice_status',
            ])
            ->orderByDesc('aq_payouts.created_at')
            ->paginate(20)
            ->withQueryString();

        $profile     = $user->profile;
        $displayName = trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')) ?: $user->email;

        return view('publisher.payouts.index', [
            'payouts'          => $payouts,
            'user'             => $user,
            'profile'          => $profile,
            'displayName'      => $displayName,
            'payoutMethod'     => $profile?->payout_method ?? 'bankwire',
            'availableBalance' => $this->availableBalance($userId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10', 'max:1000000'],
            'notes'  => ['nullable', 'string', 'max:500'],
        ]);

        $user      = $request->user()->load('profile');
        $available = $this->availableBalance($user->id);

        if ((float) $validated['amount'] > $available) {
            return back()
                ->withErrors(['amount' => 'The requested amount exceeds your available balance of €' . number_format($available, 2) . '.'])
                ->withInput();
        }
        $userId       = $user->id;
        $profileMethod = $user->profile?->payout_method ?? 'bankwire';
        $method       = self::METHOD_MAP[$profileMethod] ?? 'wire_transfer';

        $invoiceNumber = 'PUB-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        Invoice::create([
            'user_id'        => $userId,
            'invoice_number' => $invoiceNumber,
            'type'           => 'publisher_payout',
            'amount'         => $validated['amount'],
            'tax_amount'     => 0,
            'total_amount'   => $validated['amount'],
            'currency'       => 'EUR',
            'status'         => 'draft',
            'due_date'       => now()->addDays(7)->toDateString(),
        ]);

        Payout::create([
            'user_id'           => $userId,
            'amount'            => $validated['amount'],
            'currency'          => 'EUR',
            'payment_method'    => $method,
            'payment_reference' => $invoiceNumber,
            'status'            => 'pending',
            'period_start'      => now()->startOfMonth()->toDateString(),
            'period_end'        => now()->toDateString(),
            'notes'             => $validated['notes'] ?? null,
        ]);

        $this->notifier->deliver(
            $user->fresh('profile'),
            'payout_request',
            'Payout Request Submitted',
            'Your payout request of €' . number_format((float) $validated['amount'], 2) . ' has been submitted (Invoice #' . $invoiceNumber . ').',
            route('publisher.payouts')
        );

        return redirect()
            ->route('publisher.payouts')
            ->with('success', 'Payout request submitted. Invoice #' . $invoiceNumber . ' created.');
    }

    public function show(Request $request, int $id): View
    {
        $payout = Payout::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $invoice = Invoice::query()
            ->where('invoice_number', $payout->payment_reference)
            ->first();

        $user        = $request->user()->load('profile');
        $profile     = $user->profile;
        $displayName = trim(($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? '')) ?: $user->email;

        return view('publisher.payouts.show', [
            'payout'      => $payout,
            'invoice'     => $invoice,
            'user'        => $user,
            'profile'     => $profile,
            'displayName' => $displayName,
        ]);
    }
}
