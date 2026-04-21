<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $adminUsers = User::whereIn('role', ['admin', 'manager', 'operational'])->get();

        if ($adminUsers->isEmpty()) {
            $this->command->warn('No admin users found. Skipping NotificationSeeder.');
            return;
        }

        // Types must match enum: success, warning, error, info, payment, campaign, system
        $notificationTypes = [
            'campaign' => [
                ['title' => 'New Campaign Pending Approval', 'message' => 'Campaign "Summer Sale 2026" from advertiser@example.com requires your review.', 'action_url' => '/admin/approvals/campaigns'],
                ['title' => 'Campaign Approval Required', 'message' => 'A new campaign "Black Friday Deals" has been submitted for approval.', 'action_url' => '/admin/approvals/campaigns'],
                ['title' => 'Urgent: Campaign Review Needed', 'message' => 'High-budget campaign "Premium Launch" needs immediate review.', 'action_url' => '/admin/approvals/campaigns'],
                ['title' => 'Creative Pending Review', 'message' => 'New banner creative uploaded for campaign ID #1234.', 'action_url' => '/admin/approvals/creatives'],
                ['title' => 'Video Creative Submitted', 'message' => 'A 30-second video ad has been uploaded and needs approval.', 'action_url' => '/admin/approvals/creatives'],
            ],
            'payment' => [
                ['title' => 'Payment Received', 'message' => 'Advertiser deposited $5,000.00 via PayPal.', 'action_url' => '/admin/finance/deposits'],
                ['title' => 'Payout Request', 'message' => 'Publisher requested a payout of $1,250.00.', 'action_url' => '/admin/finance/payouts'],
                ['title' => 'Payment Approval Needed', 'message' => 'Wire transfer of $10,000.00 requires verification.', 'action_url' => '/admin/finance/deposits'],
            ],
            'info' => [
                ['title' => 'New Advertiser Registration', 'message' => 'New advertiser account created: newadvertiser@company.com', 'action_url' => '/admin/advertisers'],
                ['title' => 'New Publisher Signup', 'message' => 'Publisher "TechNews Daily" has registered and is pending verification.', 'action_url' => '/admin/publishers'],
                ['title' => 'New Site Submitted', 'message' => 'Publisher submitted site "example-blog.com" for approval.', 'action_url' => '/admin/approvals/sites'],
                ['title' => 'Site Verification Required', 'message' => 'Site "news-portal.net" ownership verification pending.', 'action_url' => '/admin/approvals/sites'],
            ],
            'system' => [
                ['title' => 'System Maintenance Scheduled', 'message' => 'Maintenance window scheduled for Sunday 2:00 AM - 4:00 AM UTC.', 'action_url' => '/admin/settings'],
                ['title' => 'Daily Report Ready', 'message' => 'Your daily performance report is ready for review.', 'action_url' => '/admin/reports'],
            ],
            'warning' => [
                ['title' => 'Low Balance Alert', 'message' => 'Advertiser ID #567 has a balance below $50.', 'action_url' => '/admin/advertisers/567'],
                ['title' => 'Suspicious Activity Detected', 'message' => 'Unusual click patterns detected on campaign #789.', 'action_url' => '/admin/reports/fraud'],
                ['title' => 'IP Blacklist Update', 'message' => '15 new IPs have been added to the fraud blacklist.', 'action_url' => '/admin/settings/security'],
            ],
            'success' => [
                ['title' => 'Campaign Approved', 'message' => 'Campaign "Holiday Promo" has been approved and is now live.', 'action_url' => '/admin/campaigns'],
                ['title' => 'Payout Completed', 'message' => 'Payout of $2,500.00 to Publisher #123 has been processed.', 'action_url' => '/admin/payouts'],
            ],
            'error' => [
                ['title' => 'Payment Failed', 'message' => 'Payment processing failed for advertiser deposit #456.', 'action_url' => '/admin/finance/deposits'],
                ['title' => 'API Error Detected', 'message' => 'Multiple API errors detected in the last hour.', 'action_url' => '/admin/settings'],
            ],
        ];

        $baseTime = now();
        $notificationCount = 0;

        foreach ($adminUsers as $user) {
            // Create 8-15 notifications per admin user
            $numNotifications = rand(8, 15);

            for ($i = 0; $i < $numNotifications; $i++) {
                $type = array_rand($notificationTypes);
                $templates = $notificationTypes[$type];
                $template = $templates[array_rand($templates)];

                $createdAt = $baseTime->copy()->subMinutes(rand(5, 10080)); // Within last 7 days
                $isRead = rand(0, 100) < 60; // 60% read
                $readAt = $isRead ? $createdAt->copy()->addMinutes(rand(1, 120)) : null;

                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $template['title'],
                    'message' => $template['message'],
                    'action_url' => $template['action_url'],
                    'is_read' => $isRead,
                    'read_at' => $readAt,
                    'created_at' => $createdAt,
                ]);

                $notificationCount++;
            }
        }

        $this->command->info("Created {$notificationCount} notifications for " . $adminUsers->count() . " admin users.");
    }
}
