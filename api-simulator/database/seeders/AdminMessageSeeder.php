<?php

namespace Database\Seeders;

use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminMessageSeeder extends Seeder
{
    public function run(): void
    {
        $adminUsers = User::whereIn('role', ['admin', 'manager', 'operational'])->get();

        if ($adminUsers->count() < 2) {
            $this->command->warn('Need at least 2 admin users for messages. Skipping AdminMessageSeeder.');
            return;
        }

        $messageTemplates = [
            'normal' => [
                ['subject' => 'Weekly Performance Summary', 'message' => "Hi team,\n\nHere's the weekly performance summary:\n\n- Total impressions: 2.5M\n- Click-through rate: 1.8%\n- Revenue: $12,450\n\nOverall, we're seeing steady growth. Let me know if you have any questions.\n\nBest regards"],
                ['subject' => 'New Publisher Onboarding', 'message' => "Hello,\n\nWe have a new publisher signing up from Germany. They run a network of tech blogs with approximately 500K monthly visitors.\n\nCould you please handle the verification process?\n\nThanks"],
                ['subject' => 'Campaign Optimization Suggestions', 'message' => "Hi,\n\nI've been reviewing Campaign #1234 and noticed some opportunities for optimization:\n\n1. The targeting could be narrowed to improve CTR\n2. Creative A is outperforming Creative B by 40%\n3. Peak performance is during evening hours (6-10 PM)\n\nLet me know if you'd like to discuss further."],
                ['subject' => 'Meeting Notes - Quarterly Review', 'message' => "Hi all,\n\nAttached are the key points from our quarterly review:\n\n- Q1 revenue exceeded targets by 15%\n- Publisher network grew by 23 new sites\n- Fraud detection improved, blocking 2.3% more invalid traffic\n\nAction items have been assigned in the project tracker."],
                ['subject' => 'Documentation Update', 'message' => "Hello,\n\nI've updated the API documentation with the new endpoints for the reporting module. Please review when you get a chance and let me know if anything is unclear.\n\nThe changes are live on the developer portal."],
            ],
            'high' => [
                ['subject' => 'URGENT: Large Advertiser Issue', 'message' => "Hi,\n\nOur largest advertiser (Account #789) is reporting discrepancies in their impression counts. This is a priority issue as they're considering pausing their campaigns.\n\nCan we schedule a call ASAP to investigate?\n\nThanks"],
                ['subject' => 'Payment Processing Delay', 'message' => "Team,\n\nWe're experiencing delays with PayPal payments today. The issue is being investigated by our payment provider.\n\nPlease inform any publishers who inquire that payouts will be processed by end of day.\n\nI'll update you once resolved."],
                ['subject' => 'Critical Bug Found', 'message' => "Hi,\n\nA critical bug was discovered in the tracking pixel implementation affecting mobile traffic. Engineering is working on a fix.\n\nETA for resolution: 2 hours.\n\nAffected campaigns have been paused temporarily."],
            ],
            'urgent' => [
                ['subject' => 'SYSTEM ALERT: Server Load Critical', 'message' => "URGENT!\n\nServer load has reached critical levels. Auto-scaling has been triggered but manual intervention may be needed.\n\nCurrent status:\n- CPU: 95%\n- Memory: 89%\n- Active connections: 45,000\n\nPlease monitor the situation closely."],
                ['subject' => 'Security Incident - Immediate Action Required', 'message' => "SECURITY ALERT\n\nWe've detected unauthorized access attempts from multiple IP addresses. The following actions have been taken:\n\n1. Affected IPs have been blocked\n2. Two-factor authentication has been enforced\n3. Audit logs are being reviewed\n\nPlease change your password immediately as a precaution."],
            ],
            'low' => [
                ['subject' => 'Office Supplies Order', 'message' => "Hi,\n\nJust a reminder that we're placing an office supplies order this Friday. Let me know if you need anything added to the list.\n\nThanks!"],
                ['subject' => 'Team Lunch Friday', 'message' => "Hello team,\n\nWe're organizing a team lunch this Friday at 12:30 PM. Please let me know your dietary preferences by Wednesday.\n\nLooking forward to it!"],
                ['subject' => 'Holiday Schedule Reminder', 'message' => "Hi everyone,\n\nJust a friendly reminder that the office will be closed next Monday for the national holiday. Support will be available via email for urgent matters only.\n\nEnjoy the long weekend!"],
            ],
        ];

        $baseTime = now();
        $messageCount = 0;

        // Create messages between admin users
        foreach ($adminUsers as $sender) {
            $recipients = $adminUsers->where('id', '!=', $sender->id);

            foreach ($recipients as $recipient) {
                // Create 2-5 messages per sender-recipient pair
                $numMessages = rand(2, 5);

                for ($i = 0; $i < $numMessages; $i++) {
                    $priority = $this->getRandomPriority();
                    $templates = $messageTemplates[$priority];
                    $template = $templates[array_rand($templates)];

                    $createdAt = $baseTime->copy()->subMinutes(rand(30, 20160)); // Within last 14 days
                    $isRead = rand(0, 100) < 70; // 70% read
                    $readAt = $isRead ? $createdAt->copy()->addMinutes(rand(5, 480)) : null;
                    $isArchived = $isRead && rand(0, 100) < 20; // 20% of read messages archived

                    AdminMessage::create([
                        'sender_id' => $sender->id,
                        'recipient_id' => $recipient->id,
                        'subject' => $template['subject'],
                        'message' => $template['message'],
                        'priority' => $priority,
                        'is_read' => $isRead,
                        'read_at' => $readAt,
                        'is_archived' => $isArchived,
                        'created_at' => $createdAt,
                    ]);

                    $messageCount++;
                }
            }
        }

        $this->command->info("Created {$messageCount} admin messages between " . $adminUsers->count() . " admin users.");
    }

    private function getRandomPriority(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 5) {
            return 'urgent';
        } elseif ($rand <= 20) {
            return 'high';
        } elseif ($rand <= 85) {
            return 'normal';
        } else {
            return 'low';
        }
    }
}
