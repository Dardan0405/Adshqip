@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    {{-- Welcome --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Platform Overview</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, {{ Auth::user()->email }}. Here's what's happening today.</p>
    </div>

    {{-- ═══════════ TOP STAT CARDS ═══════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Revenue --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($stats['total_revenue'], 2) }}</div>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs font-medium text-brand-600">↑ {{ $stats['revenue_change'] }}%</span>
                        <span class="text-[10px] text-gray-400">vs last month</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand-600" viewBox="0 0 24 24" fill="none"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5"/></svg>
                </div>
            </div>
        </div>

        {{-- Active Users --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">Active Users</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['active_users']) }}</div>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs font-medium text-brand-600">↑ {{ $stats['users_change'] }}%</span>
                        <span class="text-[10px] text-gray-400">vs last month</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
            </div>
        </div>

        {{-- Total Impressions --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">Impressions (30d)</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['impressions']) }}</div>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs font-medium {{ $stats['impressions_change'] >= 0 ? 'text-brand-600' : 'text-red-500' }}">{{ $stats['impressions_change'] >= 0 ? '↑' : '↓' }} {{ abs($stats['impressions_change']) }}%</span>
                        <span class="text-[10px] text-gray-400">vs last month</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" viewBox="0 0 24 24" fill="none"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke="currentColor" stroke-width="1.5"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke="currentColor" stroke-width="1.5"/></svg>
                </div>
            </div>
        </div>

        {{-- Fraud Blocked --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide">Fraud Blocked</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['fraud_blocked']) }}</div>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs font-medium text-red-500">↑ {{ $stats['fraud_change'] }}%</span>
                        <span class="text-[10px] text-gray-400">events this month</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ MIDDLE ROW: Revenue Chart + User Breakdown ═══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Revenue Chart (2/3) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Platform Revenue (30 Days)</h3>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5 text-gray-500"><span class="w-3 h-3 rounded-sm bg-brand-500"></span> Revenue</span>
                    <span class="flex items-center gap-1.5 text-gray-500"><span class="w-3 h-3 rounded-sm bg-gray-300"></span> Payouts</span>
                </div>
            </div>
            <div class="relative h-56">
                <div class="absolute inset-0 flex items-end gap-1 px-1">
                    @foreach($chartData as $day)
                        <div class="flex-1 flex flex-col items-center gap-0 group">
                            <div class="w-full rounded-t bg-brand-400 group-hover:bg-brand-500 transition-all" style="height: {{ $day['revenue_pct'] }}%"></div>
                            <div class="w-full rounded-t bg-gray-200 group-hover:bg-gray-300 transition-all" style="height: {{ $day['payout_pct'] }}%"></div>
                            @if($loop->index % 3 === 0)
                                <span class="text-[8px] text-gray-400 mt-1">{{ $day['label'] }}</span>
                            @else
                                <span class="text-[8px] text-transparent mt-1">.</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- User Breakdown (1/3) --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">User Breakdown</h3>
            <div class="space-y-4">
                @foreach($userBreakdown as $role)
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-600 capitalize">{{ $role['role'] }}</span>
                            <span class="font-semibold text-gray-900">{{ number_format($role['count']) }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-gray-100">
                            <div class="h-2 rounded-full {{ $role['color'] }}" style="width: {{ $role['pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Total Users</span>
                    <span class="font-bold text-gray-900">{{ number_format($stats['total_users']) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-2">
                    <span class="text-gray-500">New This Month</span>
                    <span class="font-bold text-brand-600">+{{ $stats['new_users_month'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════ BOTTOM ROW: Recent Users + Support Tickets + Campaign Status ═══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Users --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Recent Users</h3>
                <a href="#" class="text-xs text-brand-600 hover:underline">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($recentUsers as $user)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full {{ $user['avatar_bg'] }} flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr($user['email'], 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 truncate max-w-[140px]">{{ $user['email'] }}</p>
                                <p class="text-[10px] text-gray-400">{{ $user['joined'] }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $user['role_color'] }}">{{ $user['role'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Support Tickets --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Open Tickets</h3>
                <span class="text-xs font-semibold text-white bg-red-500 rounded-full px-2 py-0.5">{{ count($openTickets) }}</span>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($openTickets as $ticket)
                    <div class="px-6 py-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-800 truncate max-w-[180px]">{{ $ticket['subject'] }}</p>
                            @php
                                $prioColors = ['urgent' => 'bg-red-100 text-red-700', 'high' => 'bg-orange-100 text-orange-700', 'medium' => 'bg-yellow-100 text-yellow-700', 'low' => 'bg-gray-100 text-gray-600'];
                            @endphp
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $prioColors[$ticket['priority']] ?? 'bg-gray-100 text-gray-600' }}">{{ $ticket['priority'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-[10px] text-gray-400">
                            <span>{{ $ticket['category'] }}</span>
                            <span>·</span>
                            <span>{{ $ticket['created'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Campaign Status Summary --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Campaign Status</h3>
            </div>
            <div class="p-6 space-y-3">
                @foreach($campaignStatus as $status)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $status['dot'] }}"></span>
                            <span class="text-sm text-gray-600 capitalize">{{ $status['label'] }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900">{{ $status['count'] }}</span>
                    </div>
                @endforeach

                <div class="pt-3 mt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Total</span>
                        <span class="text-sm font-bold text-gray-900">{{ $stats['total_campaigns'] }}</span>
                    </div>
                </div>

                <div class="pt-3">
                    <div class="text-[10px] text-gray-400 uppercase tracking-wide mb-2">Pending Review</div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-orange-500">{{ $stats['pending_review'] }}</span>
                        <span class="text-xs text-gray-400">campaigns need approval</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
