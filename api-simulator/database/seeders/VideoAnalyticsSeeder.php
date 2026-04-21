<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VideoAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $eventMap = $this->seedVastEvents();

        $videoAds = DB::table('aq_ads')
            ->whereIn('ad_type', ['video', 'vast', 'clip'])
            ->orderBy('id')
            ->limit(3)
            ->get(['id']);

        if ($videoAds->isEmpty()) {
            $this->command?->warn('VideoAnalyticsSeeder skipped: no video/vast/clip ads found.');
            return;
        }

        DB::table('aq_video_tracking')
            ->whereIn('ad_id', $videoAds->pluck('id'))
            ->delete();

        $rows = [];
        $viewerCounter = 1;
        $baseTime = Carbon::now()->subDays(2)->startOfDay()->addHours(9);

        foreach ($videoAds as $adIndex => $ad) {
            for ($viewerOffset = 0; $viewerOffset < 4; $viewerOffset++) {
                $viewerId = 'video-viewer-' . $viewerCounter++;
                $startedAt = $baseTime->copy()->addHours($adIndex)->addMinutes($viewerOffset * 7);

                $events = [
                    ['name' => 'start', 'progress' => 0, 'minutes' => 0],
                    ['name' => 'firstQuartile', 'progress' => 25, 'minutes' => 1],
                    ['name' => 'midpoint', 'progress' => 50, 'minutes' => 2],
                    ['name' => 'thirdQuartile', 'progress' => 75, 'minutes' => 3],
                ];

                foreach ($events as $event) {
                    $rows[] = [
                        'ad_id' => $ad->id,
                        'impression_id' => null,
                        'event_id' => $eventMap[$event['name']],
                        'viewer_id' => $viewerId,
                        'progress_percent' => $event['progress'],
                        'created_at' => $startedAt->copy()->addMinutes($event['minutes']),
                    ];
                }

                $finalEvent = ($viewerOffset % 3 === 0) ? 'skip' : 'complete';

                $rows[] = [
                    'ad_id' => $ad->id,
                    'impression_id' => null,
                    'event_id' => $eventMap[$finalEvent],
                    'viewer_id' => $viewerId,
                    'progress_percent' => $finalEvent === 'complete' ? 100 : 80,
                    'created_at' => $startedAt->copy()->addMinutes(4),
                ];
            }
        }

        DB::table('aq_video_tracking')->insert($rows);

        $this->command?->info('Video analytics seed data created for ' . $videoAds->count() . ' video ads.');
    }

    private function seedVastEvents(): array
    {
        $events = [
            ['event_name' => 'start', 'description' => 'Playback started'],
            ['event_name' => 'firstQuartile', 'description' => 'Reached 25 percent'],
            ['event_name' => 'midpoint', 'description' => 'Reached 50 percent'],
            ['event_name' => 'thirdQuartile', 'description' => 'Reached 75 percent'],
            ['event_name' => 'complete', 'description' => 'Playback completed'],
            ['event_name' => 'skip', 'description' => 'Playback skipped'],
        ];

        foreach ($events as $event) {
            DB::table('aq_vast_events')->updateOrInsert(
                ['event_name' => $event['event_name']],
                [
                    'description' => $event['description'],
                    'is_trackable' => true,
                    'created_at' => now(),
                ]
            );
        }

        return DB::table('aq_vast_events')
            ->whereIn('event_name', array_column($events, 'event_name'))
            ->pluck('id', 'event_name')
            ->all();
    }
}
