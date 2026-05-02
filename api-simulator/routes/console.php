<?php

use App\Models\UserSubscription;
use App\Support\SubscriptionPaymentGateway;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:renew', function (SubscriptionPaymentGateway $gateway) {
    $created = 0;

    UserSubscription::with('plan')
        ->dueForManualRenewal()
        ->chunkById(100, function ($subscriptions) use ($gateway, &$created) {
            foreach ($subscriptions as $subscription) {
                if ($gateway->createManualRenewalInvoice($subscription)) {
                    $created++;
                }
            }
        });

    $this->info("Created {$created} subscription renewal invoice(s).");
})->purpose('Create renewal invoices for subscriptions without provider-side recurring billing');

Schedule::command('subscriptions:renew')->dailyAt('02:00');
