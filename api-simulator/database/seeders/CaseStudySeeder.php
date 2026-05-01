<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use Illuminate\Database\Seeder;

class CaseStudySeeder extends Seeder
{
    public function run(): void
    {
        $caseStudies = [
            [
                'slug' => 'lajmeri-ecpm-lift',
                'title' => 'Publisher revenue lift',
                'audience_type' => 'publisher',
                'industry' => 'News portal',
                'metric_value' => '+42% eCPM',
                'metric_label' => 'Revenue increase',
                'description' => 'Major Albanian news portal switched to native feeds and interstitials with frequency caps. UX complaints dropped to near zero while revenue increased significantly.',
                'content' => "Lajmeri.al needed to raise monetization without adding intrusive placements that would hurt reader trust.\n\nAdshqip introduced native feed inventory, controlled interstitial timing, and frequency caps across repeat sessions. The team monitored eCPM, bounce behavior, and complaint volume during rollout.\n\nThe result was a 42% eCPM lift while keeping user experience stable. The publisher now manages campaign quality and ad density from a single workflow.",
                'company_name' => 'Lajmeri.al',
                'client_name' => 'Lajmeri.al',
                'logo_url' => './Lajmëri.png',
                'accent_color' => '#e11d48',
                'chart_type' => 'comparison',
                'before_label' => 'Before',
                'before_value' => '$3.1',
                'after_label' => 'After',
                'after_value' => '$4.4',
                'cta_url' => '#contact',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'shqipstore-roas-growth',
                'title' => 'eCommerce ROAS growth',
                'audience_type' => 'advertiser',
                'industry' => 'eCommerce',
                'metric_value' => '1.8x ROAS',
                'metric_label' => 'Campaign performance',
                'description' => 'Leading Albanian eCommerce store implemented CPC bidding with city-level targeting in AL and XK, plus creative A/B testing via API.',
                'content' => "ShqipStore.com wanted better acquisition efficiency across Albania and Kosovo without wasting budget on broad targeting.\n\nThe campaign moved to CPC bidding with city-level targeting, creative A/B testing, and API-based reporting. Underperforming creatives were paused quickly while winning combinations received more delivery.\n\nAfter six weeks, ROAS reached 1.8x with cleaner reporting and faster optimization cycles.",
                'company_name' => 'ShqipStore.com',
                'client_name' => 'ShqipStore.com',
                'logo_url' => './ShqipStore.png',
                'accent_color' => '#06b6d4',
                'chart_type' => 'line',
                'before_label' => 'Week 1',
                'before_value' => '1.0x',
                'after_label' => 'Week 6',
                'after_value' => '1.8x ROAS',
                'cta_url' => '#contact',
                'is_published' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'argetim-revenue-growth',
                'title' => 'Entertainment revenue growth',
                'audience_type' => 'publisher',
                'industry' => 'Entertainment',
                'metric_value' => '+28% Revenue',
                'metric_label' => 'Overall ad revenue',
                'description' => 'Entertainment portal implemented layer interstitial on navigation with smart timing and consent-aware delivery, boosting overall ad revenue.',
                'content' => "Argetim.al needed a revenue lift from high-intent navigation moments while keeping the site acceptable for repeat visitors.\n\nAdshqip configured layer interstitials with smart timing, consent-aware delivery, and audience pacing. The implementation avoided showing ads too frequently to returning users.\n\nThe portal produced a 28% revenue increase while preserving a cleaner browsing flow.",
                'company_name' => 'Argetim.al',
                'client_name' => 'Argetim.al',
                'logo_url' => './Argëtim.png',
                'accent_color' => '#f59e0b',
                'chart_type' => 'bar',
                'before_label' => 'Before',
                'before_value' => '100',
                'after_label' => 'After',
                'after_value' => '+28%',
                'cta_url' => '#contact',
                'is_published' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($caseStudies as $caseStudy) {
            CaseStudy::updateOrCreate(
                ['slug' => $caseStudy['slug']],
                $caseStudy
            );
        }
    }
}
