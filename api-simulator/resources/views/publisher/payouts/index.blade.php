@extends('layouts.publisher')

@section('title', 'Request Payment')

@section('content')
<div class="space-y-6" x-data="payoutPage({{ (float) $availableBalance }})"
>

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-900 px-6 py-7 text-white shadow-sm">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(52,211,153,0.25),_transparent_55%)]"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <span class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-100">Payments</span>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight">Request Payment</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-slate-200/85">
                    Submit a payout request for your publisher earnings. Each request generates an invoice that is reviewed and processed by our finance team.
                </p>
            </div>
            <div>
                <button @click="maxBalance > 0 && (openModal = true)"
                        :class="maxBalance > 0 ? 'bg-emerald-500 hover:bg-emerald-400 cursor-pointer' : 'bg-emerald-800 opacity-50 cursor-not-allowed'"
                        :title="maxBalance > 0 ? 'Request payout' : 'No available balance to withdraw'"
                        class="flex items-center gap-2 rounded-xl text-white px-5 py-3 text-sm font-semibold transition-colors shadow-lg shadow-emerald-900/40">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    Payout
                </button>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Info bar: method + available balance --}}
    @php $methodLabels = ['bankwire'=>'Bank Wire','paypal'=>'PayPal','bitcoin'=>'Bitcoin','stripe'=>'Stripe','authorize_net'=>'Authorize.net']; @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {{-- Payout method --}}
        <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-5 py-3.5 shadow-sm">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-xs text-gray-500">Active payout method</p>
                <p class="text-sm font-semibold text-gray-800">{{ $methodLabels[$payoutMethod] ?? ucfirst($payoutMethod) }}</p>
            </div>
            <a href="{{ route('publisher.payment-settings') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium transition-colors">Change</a>
        </div>
        {{-- Available balance --}}
        <div class="flex items-center gap-3 rounded-xl border {{ $availableBalance > 0 ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }} px-5 py-3.5 shadow-sm">
            <div class="w-9 h-9 rounded-xl {{ $availableBalance > 0 ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 {{ $availableBalance > 0 ? 'text-emerald-600' : 'text-gray-400' }}" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-xs text-gray-500">Available to withdraw</p>
                <p class="text-lg font-bold {{ $availableBalance > 0 ? 'text-emerald-700' : 'text-gray-400' }}">
                    €{{ number_format($availableBalance, 2) }}
                </p>
            </div>
            @if($availableBalance > 0)
                <button @click="openModal = true"
                        class="text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 rounded-lg px-3 py-1.5 transition-colors">
                    Withdraw
                </button>
            @endif
        </div>
    </div>

    {{-- Table card --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50/60">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span class="font-semibold text-gray-800">{{ $payouts->total() }}</span> payout requests
            </div>
            <span class="text-xs text-gray-400">Page {{ $payouts->currentPage() }} of {{ $payouts->lastPage() }}</span>
        </div>

        @if($payouts->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <p class="text-base font-medium text-gray-500">No payout requests yet</p>
                <p class="text-sm text-gray-400 mt-1">Click <strong>Payout</strong> to submit your first request.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-5 py-3 w-16">#</th>
                            <th class="px-4 py-3 w-36">Date</th>
                            <th class="px-4 py-3 w-48">Invoice Period</th>
                            <th class="px-4 py-3 w-28">Invoice Status</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3 w-40">Invoice #</th>
                            <th class="px-4 py-3 w-32 text-right">Invoice Amount</th>
                            <th class="px-4 py-3 w-20 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($payouts as $row)
                            @php
                                $statusColors = [
                                    'draft'     => 'bg-gray-100 text-gray-600 ring-gray-200',
                                    'sent'      => 'bg-blue-50 text-blue-700 ring-blue-200',
                                    'paid'      => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'overdue'   => 'bg-red-50 text-red-700 ring-red-200',
                                    'cancelled' => 'bg-gray-100 text-gray-500 ring-gray-200',
                                    'pending'   => 'bg-amber-50 text-amber-700 ring-amber-200',
                                    'processing'=> 'bg-blue-50 text-blue-700 ring-blue-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                    'failed'    => 'bg-red-50 text-red-700 ring-red-200',
                                ];
                                $status = $row->invoice_status ?? $row->payout_status;
                                $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-5 py-3 text-xs font-mono text-gray-400">{{ $row->id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs font-medium text-gray-700">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</span>
                                    <span class="block text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($row->period_start && $row->period_end)
                                        <span class="text-xs text-gray-700">
                                            {{ \Carbon\Carbon::parse($row->period_start)->format('d M Y') }}
                                            <span class="text-gray-400 mx-1">→</span>
                                            {{ \Carbon\Carbon::parse($row->period_end)->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusClass }}">
                                        {{ ucfirst($status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-800 font-medium">{{ $displayName }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-600">{{ $row->invoice_number ?? $row->payment_reference ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-sm font-semibold text-gray-800">
                                        €{{ number_format((float)($row->invoice_amount ?? $row->amount), 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('publisher.payouts.show', $row->id) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-colors">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($payouts->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400">Showing {{ $payouts->firstItem() }}–{{ $payouts->lastItem() }} of {{ $payouts->total() }}</span>
                    <div class="flex items-center gap-1">
                        @if($payouts->onFirstPage())
                            <span class="px-3 py-1.5 text-xs text-gray-300 rounded-lg border border-gray-100">Previous</span>
                        @else
                            <a href="{{ $payouts->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Previous</a>
                        @endif
                        @foreach($payouts->getUrlRange(max(1,$payouts->currentPage()-2), min($payouts->lastPage(),$payouts->currentPage()+2)) as $page => $url)
                            @if($page == $payouts->currentPage())
                                <span class="px-3 py-1.5 text-xs font-semibold text-white bg-brand-600 rounded-lg">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if($payouts->hasMorePages())
                            <a href="{{ $payouts->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors">Next</a>
                        @else
                            <span class="px-3 py-1.5 text-xs text-gray-300 rounded-lg border border-gray-100">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Payout Request Modal --}}
    <div x-show="openModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" style="display:none;">
        <div @click.away="openModal = false" class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md">

            <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Request Payout</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Enter the amount you'd like to withdraw.</p>
                    </div>
                    <button @click="openModal = false" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('publisher.payouts.store') }}" class="px-6 py-5 space-y-4">
                @csrf

                {{-- Available balance hint in modal --}}
                <div class="flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-2.5">
                    <span class="text-xs text-emerald-700 font-medium">Available balance</span>
                    <span class="text-sm font-bold text-emerald-700">€{{ number_format($availableBalance, 2) }}</span>
                </div>

                {{-- Amount --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm font-medium text-gray-700">Payout Amount <span class="text-red-500">*</span></label>
                        <button type="button" @click="setMax()"
                                class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg px-2 py-0.5 transition-colors">
                            Max
                        </button>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">€</span>
                        <input type="number" name="amount" id="payoutAmount" min="10"
                               :max="maxBalance" step="0.01" placeholder="0.00"
                               x-model="amount"
                               class="w-full pl-7 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('amount') border-red-400 @enderror">
                    </div>
                    @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs mt-1" :class="overBalance ? 'text-red-500 font-medium' : 'text-gray-400'">
                        <span x-show="!overBalance">Minimum €10.00 · Maximum €<span x-text="maxBalance.toFixed(2)"></span></span>
                        <span x-show="overBalance">Amount exceeds your available balance of €<span x-text="maxBalance.toFixed(2)"></span></span>
                    </p>
                </div>

                {{-- Payout method display --}}
                <div class="flex items-center gap-3 rounded-xl bg-gray-50 border border-gray-200 px-4 py-3">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Payout via</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $methodLabels[$payoutMethod] ?? ucfirst($payoutMethod) }}</p>
                    </div>
                    <a href="{{ route('publisher.payment-settings') }}" class="text-xs text-brand-600 hover:underline">Change</a>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes <span class="text-gray-400 text-xs">(optional)</span></label>
                    <textarea name="notes" rows="2" placeholder="Any additional notes for this request…"
                              class="w-full px-3 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="openModal = false"
                            class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="overBalance || amount < 10"
                            :class="(overBalance || amount < 10) ? 'opacity-50 cursor-not-allowed bg-emerald-600' : 'hover:bg-emerald-700'"
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl transition-colors">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function payoutPage(maxBalance) {
    return {
        openModal: {{ $errors->any() ? 'true' : 'false' }},
        maxBalance: maxBalance,
        amount: {{ old('amount') ? (float) old('amount') : 0 }},

        get overBalance() {
            return this.amount > this.maxBalance;
        },

        setMax() {
            this.amount = Math.floor(this.maxBalance * 100) / 100;
        },
    };
}
</script>
@endpush
