<?php

namespace App\Support;

use App\Models\Invoice;
use App\Models\PlatformSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;

class AdvertiserPaymentManager
{
    public function createDeposit(User $advertiser, float $amount, ?string $paymentType = null, string $currency = 'EUR', string $status = 'completed', ?string $description = null): Transaction
    {
        $paymentType = $this->normalizePaymentType($paymentType ?: PlatformSetting::getAdvertiserPaymentType());
        $storageGateway = $this->storageGateway($paymentType);
        $gatewayStatus = $this->gatewayStatus($status);
        $amount = round(max(0, $amount), 4);

        return DB::transaction(function () use ($advertiser, $amount, $storageGateway, $gatewayStatus, $currency, $status, $description) {
            /** @var UserProfile $profile */
            $profile = $advertiser->profile()->firstOrCreate(
                ['user_id' => $advertiser->id],
                ['balance' => 0]
            );

            $balanceBefore = (float) ($profile->balance ?? 0);
            $balanceAfter = $status === 'completed' ? $balanceBefore + $amount : $balanceBefore;

            $invoice = Invoice::create([
                'user_id' => $advertiser->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'type' => 'advertiser_charge',
                'amount' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'currency' => $currency,
                'status' => $status === 'completed' ? 'paid' : 'sent',
                'due_date' => now()->toDateString(),
                'paid_at' => $status === 'completed' ? now() : null,
            ]);

            $transaction = Transaction::create([
                'user_id' => $advertiser->id,
                'type' => 'deposit',
                'amount' => $amount,
                'currency' => $currency,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_gateway' => $storageGateway,
                'gateway_status' => $gatewayStatus,
                'invoice_id' => $invoice->id,
                'description' => $description ?: 'Advertiser deposit',
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            if ($status === 'completed') {
                $profile->forceFill(['balance' => $balanceAfter])->save();
            }

            return $transaction->fresh();
        });
    }

    public function paymentTypeOptions(): array
    {
        return PlatformSetting::getAdvertiserPaymentTypes();
    }

    public function activePaymentType(): string
    {
        return PlatformSetting::getAdvertiserPaymentType();
    }

    public function activePaymentDetails(): array
    {
        return PlatformSetting::getPaymentSettingsFor($this->activePaymentType());
    }

    public function paymentTypeLabel(?string $paymentType): string
    {
        $paymentType = $this->normalizePaymentType($paymentType);

        return $this->paymentTypeOptions()[$paymentType] ?? str_replace('_', ' ', ucfirst($paymentType ?: 'wire_transfer'));
    }

    public function normalizePaymentType(?string $paymentType): string
    {
        return match ($paymentType) {
            'stripe', 'credit_card' => PlatformSetting::ADVERTISER_PAYMENT_STRIPE,
            'authorize' => PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE,
            'coinbase', 'crypto', 'bitcoin' => PlatformSetting::ADVERTISER_PAYMENT_BITCOIN,
            'manual', 'wire_transfer' => PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER,
            default => in_array($paymentType, array_keys($this->paymentTypeOptions()), true)
                ? $paymentType
                : PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER,
        };
    }

    public function storageGateway(?string $paymentType): string
    {
        return match ($this->normalizePaymentType($paymentType)) {
            PlatformSetting::ADVERTISER_PAYMENT_STRIPE => 'stripe',
            PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => 'authorize',
            PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => 'bitcoin',
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => 'paypal',
            default => 'wire_transfer',
        };
    }

    public function gatewayStatus(string $status): string
    {
        return match ($status) {
            'completed' => 'confirmed',
            'failed' => 'failed',
            'reversed' => 'refunded',
            default => 'pending',
        };
    }

    private function nextInvoiceNumber(): string
    {
        $latestId = (int) (Invoice::max('id') ?? 0) + 1;

        return 'ADV-' . now()->format('Ymd') . '-' . str_pad((string) $latestId, 6, '0', STR_PAD_LEFT);
    }
}
