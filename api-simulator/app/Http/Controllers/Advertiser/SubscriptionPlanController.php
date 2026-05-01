<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PricingPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $cycle = $request->query('cycle') === 'yearly' ? 'yearly' : 'monthly';
        $userId = $request->user()->id;

        $plans = PricingPlan::query()
            ->active()
            ->whereIn('target_audience', ['advertiser', 'both'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentSubscription = UserSubscription::query()
            ->with('plan')
            ->forUser($userId)
            ->current()
            ->orderByDesc('current_period_end')
            ->orderByDesc('id')
            ->first();

        $subscriptionHistory = UserSubscription::query()
            ->with('plan')
            ->forUser($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('advertiser.subscriptions.index', [
            'plans' => $plans,
            'cycle' => $cycle,
            'currentSubscription' => $currentSubscription,
            'subscriptionHistory' => $subscriptionHistory,
        ]);
    }

    public function subscribe(Request $request, PricingPlan $plan)
    {
        $data = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        if ($plan->status !== 'active' || ! in_array($plan->target_audience, ['advertiser', 'both'], true)) {
            return back()->withErrors(['plan' => 'This subscription plan is not available for advertisers.']);
        }

        $user = $request->user();
        $cycle = $data['billing_cycle'];
        $price = $cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $periodStart = now()->toDateString();
        $periodEnd = $cycle === 'yearly' ? now()->addYear()->toDateString() : now()->addMonth()->toDateString();
        $createdInvoice = null;

        DB::transaction(function () use ($user, $plan, $cycle, $price, $periodStart, $periodEnd, &$createdInvoice) {
            UserSubscription::query()
                ->forUser($user->id)
                ->current()
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            UserSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'status' => 'active',
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
            ]);

            if ($price !== null && (float) $price > 0) {
                $createdInvoice = Invoice::create([
                    'user_id' => $user->id,
                    'invoice_number' => $this->invoiceNumber($user->id, $plan->id),
                    'type' => 'advertiser_charge',
                    'amount' => $price,
                    'tax_amount' => 0,
                    'total_amount' => $price,
                    'currency' => $plan->currency ?: 'EUR',
                    'status' => 'sent',
                    'due_date' => now()->addDays(7)->toDateString(),
                ]);
            }
        });

        $message = 'Subscription plan updated.';
        if ($createdInvoice) {
            $message .= ' Invoice ' . $createdInvoice->invoice_number . ' was created in Invoice History.';
        }

        return redirect()
            ->route('advertiser.payments.subscription-plan', ['cycle' => $cycle])
            ->with('success', $message);
    }

    public function cancel(Request $request, UserSubscription $subscription)
    {
        if ((int) $subscription->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        if (! in_array($subscription->status, ['active', 'trial'], true)) {
            return back()->withErrors(['subscription' => 'This subscription is already closed.']);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('advertiser.payments.subscription-plan')
            ->with('success', 'Subscription cancelled.');
    }

    private function invoiceNumber(int $userId, int $planId): string
    {
        return 'SUB-' . now()->format('YmdHis') . '-' . $userId . '-' . $planId . '-' . random_int(1000, 9999);
    }
}
