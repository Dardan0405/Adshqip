<?php

namespace Database\Seeders;

use App\Models\TelegramMiniApp;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MobileApplicationApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $owners = [
            [
                'email' => 'mobile.pending@adshqip.com',
                'app_name' => 'Adshqip Mobile Wallet',
                'app_short_name' => 'adwalletdemo',
                'bot_username' => 'AdshqipWalletBot',
                'app_url' => 'https://apps.adshqip.com/mobile-wallet',
                'category' => 'campaign_manager',
                'status' => 'pending_review',
                'admin_approved' => false,
                'rejection_reason' => null,
            ],
            [
                'email' => 'mobile.approved@adshqip.com',
                'app_name' => 'Adshqip Publisher Hub',
                'app_short_name' => 'publisherhub',
                'bot_username' => 'AdshqipPublisherHubBot',
                'app_url' => 'https://apps.adshqip.com/publisher-hub',
                'category' => 'analytics',
                'status' => 'active',
                'admin_approved' => true,
                'rejection_reason' => null,
            ],
            [
                'email' => 'mobile.rejected@adshqip.com',
                'app_name' => 'Adshqip Promo Studio',
                'app_short_name' => 'promostudio',
                'bot_username' => 'AdshqipPromoStudioBot',
                'app_url' => 'https://apps.adshqip.com/promo-studio',
                'category' => 'ad_preview',
                'status' => 'suspended',
                'admin_approved' => false,
                'rejection_reason' => 'Creative assets and flow need revision before activation.',
            ],
        ];

        foreach ($owners as $definition) {
            $user = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'password_hash' => Hash::make('password123'),
                    'role' => 'advertiser',
                    'status' => 'active',
                    'preferred_language' => 'en',
                    'theme_preference' => 'system',
                    'timezone' => 'Europe/Tirane',
                    'is_deleted' => false,
                ]
            );

            TelegramMiniApp::updateOrCreate(
                ['app_short_name' => $definition['app_short_name']],
                [
                    'user_id' => $user->id,
                    'app_name' => $definition['app_name'],
                    'bot_username' => $definition['bot_username'],
                    'bot_token_hash' => Hash::make($definition['bot_username'] . '-token'),
                    'app_url' => $definition['app_url'],
                    'icon_url' => 'https://cdn.adshqip.com/mobile-apps/icon-' . $definition['app_short_name'] . '.png',
                    'description' => 'Seeded mobile application used for admin approval workflow testing.',
                    'category' => $definition['category'],
                    'webhook_url' => $definition['app_url'] . '/webhook',
                    'webhook_secret_hash' => Hash::make($definition['app_short_name'] . '-secret'),
                    'allowed_origins' => [$definition['app_url']],
                    'theme_params' => ['bg_color' => '#ffffff', 'text_color' => '#111827'],
                    'inline_mode_enabled' => false,
                    'payment_enabled' => false,
                    'payment_provider_token_hash' => null,
                    'menu_button_enabled' => true,
                    'expand_on_open' => true,
                    'status' => $definition['status'],
                    'admin_approved' => $definition['admin_approved'],
                    'rejection_reason' => $definition['rejection_reason'],
                    'total_sessions' => match ($definition['status']) {
                        'active' => 1240,
                        'suspended' => 140,
                        default => 0,
                    },
                    'total_events' => match ($definition['status']) {
                        'active' => 5820,
                        'suspended' => 430,
                        default => 0,
                    },
                    'last_active_at' => $definition['status'] === 'active' ? now()->subHours(3) : null,
                    'is_deleted' => false,
                ]
            );
        }
    }
}
