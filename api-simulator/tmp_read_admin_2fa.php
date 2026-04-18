<?php

declare(strict_types=1);

use App\Models\User;

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::find(1);

echo json_encode([
    'email' => $user?->email,
    'two_factor_enabled' => $user?->two_factor_enabled,
    'two_factor_email' => $user?->two_factor_email,
    'two_factor_phone' => $user?->two_factor_phone,
    'two_factor_token_types' => $user?->two_factor_token_types,
    'two_factor_verification_options' => $user?->two_factor_verification_options,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
