<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class AdminCurrency
{
    public function code(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'EUR';
        }

        $profile = $user->relationLoaded('profile') ? $user->profile : $user->profile()->first();
        $currency = strtoupper((string) ($profile?->currency ?? 'EUR'));

        return in_array($currency, ['EUR', 'USD', 'GBP', 'ALL', 'CHF', 'CAD'], true) ? $currency : 'EUR';
    }

    public function symbol(): string
    {
        return match ($this->code()) {
            'USD' => '$',
            'GBP' => '£',
            'ALL' => 'Lek',
            'CHF' => 'CHF',
            'CAD' => 'C$',
            default => '€',
        };
    }

    public function format(float|int|string|null $amount, int $decimals = 2, bool $withCode = false): string
    {
        $numeric = is_numeric($amount) ? (float) $amount : 0.0;
        $formatted = number_format($numeric, $decimals);

        if ($withCode) {
            return $this->code() . ' ' . $formatted;
        }

        $symbol = $this->symbol();

        if (in_array($this->code(), ['ALL', 'CHF'], true)) {
            return $symbol . ' ' . $formatted;
        }

        return $symbol . $formatted;
    }
}
