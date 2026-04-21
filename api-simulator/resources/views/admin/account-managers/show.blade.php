@extends('layouts.admin')
@section('title', 'Account Manager - ' . $manager->email)
@section('content')
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.account-managers') }}" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Account Manager</h1>
            <p class="text-sm text-gray-500">{{ $manager->email }}</p>
        </div>
    </div>

    {{-- Manager Info Card --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-bold text-xl">
                {{ strtoupper(substr($manager->email, 0, 2)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-semibold text-gray-900">{{ $manager->email }}</h2>
                <div class="flex items-center gap-3 mt-1">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold bg-indigo-100 text-indigo-700">{{ ucfirst($manager->role) }}</span>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $manager->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($manager->status) }}</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-brand-600">{{ $managedAccounts->total() }}</div>
                <div class="text-sm text-gray-500">Managed Members</div>
            </div>
            <div class="flex flex-col gap-2">
                <a href="{{ route('admin.advertisers', ['account_manager' => $manager->id]) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-center">
                    Manage Advertisers
                </a>
                <a href="{{ route('admin.publishers', ['account_manager' => $manager->id]) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-center">
                    Manage Publishers
                </a>
            </div>
        </div>
    </div>

    {{-- Managed Accounts Table --}}
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Managed Accounts</h3>
            <p class="text-xs text-gray-500 mt-0.5">All publishers and advertisers assigned to this account manager</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Member</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Role</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Company</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Joined</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($managedAccounts as $member)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-semibold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($member->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $member->profile?->first_name ? $member->profile->first_name . ' ' . $member->profile->last_name : $member->email }}</div>
                                        <div class="text-xs text-gray-500">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $member->role === 'advertiser' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">{{ ucfirst($member->role) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $member->profile?->company_name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $member->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($member->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $member->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ $member->role === 'advertiser' ? route('admin.advertisers', ['search' => $member->email]) : route('admin.publishers', ['search' => $member->email]) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 mr-2">
                                    Manage
                                </a>
                                <form method="POST" action="{{ route('admin.account-managers.assign') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="user_ids[]" value="{{ $member->id }}">
                                    <input type="hidden" name="account_manager_id" value="">
                                    <button type="submit" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">No members assigned to this account manager.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($managedAccounts->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $managedAccounts->links() }}</div>
        @endif
    </div>
@endsection
