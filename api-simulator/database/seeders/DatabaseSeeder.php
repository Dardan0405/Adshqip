<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SecurityQuestionSeeder::class,
            UserSeeder::class,
            CampaignGroupSeeder::class,
            PixelTrackerSeeder::class,
            CampaignSeeder::class,
            CampaignApprovalSeeder::class,
            ManageAdMarketCampaignSeeder::class,
            AdCreativeSeeder::class,
            CreativeApprovalSeeder::class,
            MobileApplicationApprovalSeeder::class,
            AdStatsSeeder::class,
            SiteUrlReportSeeder::class,
            ZoneLimitationSeeder::class,
            BalanceSheetSeeder::class,
            AdvertiserDepositSeeder::class,
            AdvertiserPaymentHistorySeeder::class,
            AdvertiserPaymentApprovalSeeder::class,
            DirectCampaignApprovalSeeder::class,
            DirectCampaignRequestApprovalSeeder::class,
            PublisherEarningsSeeder::class,
            PublisherInvoiceSeeder::class,
            PayoutSeeder::class,
            VideoAnalyticsSeeder::class,
            NotificationSeeder::class,
            AdminMessageSeeder::class,
        ]);
    }
}
