<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\StatDaily;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $currentMonthStart = $today->copy()->startOfMonth();
        $previousMonthStart = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $previousMonthEnd = $today->copy()->subMonthNoOverflow()->endOfMonth();
        $last30Start = $today->copy()->subDays(29);
        $previous30Start = $today->copy()->subDays(59);
        $previous30End = $today->copy()->subDays(30);

        $currentMonthRevenue = (float) StatDaily::whereBetween('date', [$currentMonthStart, $today])->sum('revenue');
        $previousMonthRevenue = (float) StatDaily::whereBetween('date', [$previousMonthStart, $previousMonthEnd])->sum('revenue');
        $last30Impressions = (int) StatDaily::whereBetween('date', [$last30Start, $today])->sum('impressions');
        $previous30Impressions = (int) StatDaily::whereBetween('date', [$previous30Start, $previous30End])->sum('impressions');
        $currentMonthFraud = (int) StatDaily::whereBetween('date', [$currentMonthStart, $today])->sum('adblock_detected');
        $previousMonthFraud = (int) StatDaily::whereBetween('date', [$previousMonthStart, $previousMonthEnd])->sum('adblock_detected');

        $activeUsers = User::query()
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->count();

        $activeUsersLastMonth = User::query()
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->where('created_at', '<=', $previousMonthEnd)
            ->count();

        $stats = [
            'total_revenue' => (float) StatDaily::sum('revenue'),
            'revenue_change' => $this->percentageChange($currentMonthRevenue, $previousMonthRevenue),
            'active_users' => $activeUsers,
            'users_change' => $this->percentageChange($activeUsers, $activeUsersLastMonth),
            'impressions' => $last30Impressions,
            'impressions_change' => $this->percentageChange($last30Impressions, $previous30Impressions),
            'fraud_blocked' => $currentMonthFraud,
            'fraud_change' => $this->percentageChange($currentMonthFraud, $previousMonthFraud),
            'total_users' => User::where('is_deleted', false)->count(),
            'new_users_month' => User::where('is_deleted', false)
                ->whereBetween('created_at', [$currentMonthStart, $today->copy()->endOfDay()])
                ->count(),
            'total_campaigns' => Campaign::where('is_deleted', false)->count(),
            'pending_review' => Campaign::where('is_deleted', false)
                ->where('status', 'pending_review')
                ->count(),
        ];

        $chartData = $this->revenueChartData($last30Start, $today);
        $userBreakdown = $this->userBreakdown((int) $stats['total_users']);
        $recentUsers = $this->recentUsers();
        $openTickets = $this->openTickets();
        $campaignStatus = $this->campaignStatus();

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'userBreakdown',
            'recentUsers',
            'openTickets',
            'campaignStatus'
        ));
    }

    private function revenueChartData(Carbon $startDate, Carbon $endDate): array
    {
        $dailyFinancials = StatDaily::query()
            ->selectRaw('date, SUM(revenue) as revenue, SUM(publisher_earnings) as payouts')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn (StatDaily $row) => $row->date->toDateString());

        $maxChartValue = max(1, (float) $dailyFinancials->max(fn ($row) => max((float) $row->revenue, (float) $row->payouts)));

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->map(function (Carbon $date) use ($dailyFinancials, $maxChartValue) {
                $row = $dailyFinancials->get($date->toDateString());
                $revenue = (float) ($row->revenue ?? 0);
                $payouts = (float) ($row->payouts ?? 0);

                return [
                    'label' => $date->format('d M'),
                    'revenue' => $revenue,
                    'payouts' => $payouts,
                    'revenue_pct' => $this->chartPercent($revenue, $maxChartValue),
                    'payout_pct' => $this->chartPercent($payouts, $maxChartValue),
                ];
            })
            ->values()
            ->all();
    }

    private function userBreakdown(int $totalUsers): array
    {
        $roleColors = [
            'advertiser' => 'bg-blue-500',
            'publisher' => 'bg-green-500',
            'admin' => 'bg-indigo-500',
            'manager' => 'bg-orange-500',
            'operational' => 'bg-cyan-500',
        ];

        $safeTotal = max(1, $totalUsers);

        return User::query()
            ->select('role', DB::raw('COUNT(*) as count'))
            ->where('is_deleted', false)
            ->groupBy('role')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($role) => [
                'role' => $role->role,
                'count' => (int) $role->count,
                'pct' => round(((int) $role->count / $safeTotal) * 100, 1),
                'color' => $roleColors[$role->role] ?? 'bg-gray-500',
            ])
            ->all();
    }

    private function recentUsers(): array
    {
        $roleColors = [
            'advertiser' => 'bg-blue-500',
            'publisher' => 'bg-green-500',
            'admin' => 'bg-indigo-500',
            'manager' => 'bg-orange-500',
            'operational' => 'bg-cyan-500',
        ];

        $roleBadgeColors = [
            'advertiser' => 'bg-blue-100 text-blue-700',
            'publisher' => 'bg-green-100 text-green-700',
            'admin' => 'bg-indigo-100 text-indigo-700',
            'manager' => 'bg-orange-100 text-orange-700',
            'operational' => 'bg-cyan-100 text-cyan-700',
        ];

        return User::query()
            ->where('is_deleted', false)
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'email', 'role', 'created_at'])
            ->map(fn (User $recentUser) => [
                'email' => $recentUser->email,
                'role' => $recentUser->role,
                'joined' => $recentUser->created_at?->diffForHumans() ?? 'Unknown',
                'avatar_bg' => $roleColors[$recentUser->role] ?? 'bg-gray-500',
                'role_color' => $roleBadgeColors[$recentUser->role] ?? 'bg-gray-100 text-gray-700',
                'url' => $this->userUrl($recentUser),
            ])
            ->all();
    }

    private function openTickets(): array
    {
        return SupportTicket::query()
            ->whereIn('status', ['open', 'in_progress', 'waiting_reply'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'subject', 'priority', 'category', 'created_at'])
            ->map(fn (SupportTicket $ticket) => [
                'subject' => $ticket->subject,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'created' => $ticket->created_at?->diffForHumans() ?? 'Unknown',
                'url' => route('admin.support-tickets.show', $ticket),
            ])
            ->all();
    }

    private function campaignStatus(): array
    {
        $statusDots = [
            'active' => 'bg-green-500',
            'paused' => 'bg-yellow-500',
            'draft' => 'bg-gray-400',
            'completed' => 'bg-blue-500',
            'rejected' => 'bg-red-500',
            'pending_review' => 'bg-orange-500',
        ];

        $campaignCounts = Campaign::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->where('is_deleted', false)
            ->groupBy('status')
            ->pluck('count', 'status');

        return collect(['active', 'paused', 'draft', 'completed', 'rejected', 'pending_review'])
            ->map(fn (string $status) => [
                'label' => str_replace('_', ' ', $status),
                'count' => (int) ($campaignCounts[$status] ?? 0),
                'dot' => $statusDots[$status] ?? 'bg-gray-400',
            ])
            ->all();
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function chartPercent(float $value, float $max): float
    {
        if ($value <= 0.0 || $max <= 0.0) {
            return 0.0;
        }

        return round(max(4, ($value / $max) * 100), 1);
    }

    private function userUrl(User $user): string
    {
        return match ($user->role) {
            'advertiser' => route('admin.advertisers.show', $user->id),
            'publisher' => route('admin.publishers.show', $user->id),
            default => route('admin.notifications', ['user_id' => $user->id]),
        };
    }
}
