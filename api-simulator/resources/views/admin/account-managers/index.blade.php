@extends('layouts.admin')
@section('title', 'Account Managers')
@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Account Managers</h1>
        <p class="text-sm text-gray-500">Assign dedicated admin support to publishers and advertisers for personalized account management.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
        @foreach([
            ['label' => 'Total Members', 'value' => number_format($summary['total_members']), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Assigned', 'value' => number_format($summary['assigned']), 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'emerald'],
            ['label' => 'Unassigned', 'value' => number_format($summary['unassigned']), 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'amber'],
            ['label' => 'Active Managers', 'value' => number_format($summary['active_managers']), 'icon' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'indigo'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-{{ $card['color'] ?? 'gray' }}-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $card['color'] ?? 'gray' }}-600" viewBox="0 0 24 24" fill="none"><path d="{{ $card['icon'] }}" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ $card['value'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[320px,1fr]">
        {{-- Account Managers List --}}
        <div class="rounded-xl border border-gray-200 bg-white h-fit">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Account Managers</h2>
                <p class="text-xs text-gray-500 mt-0.5">Admins who can manage member accounts</p>
            </div>
            <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                @forelse($accountManagers as $manager)
                    <a href="{{ route('admin.account-managers.show', $manager) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                        <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-sm flex-shrink-0">
                            {{ strtoupper(substr($manager->email, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ $manager->email }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst($manager->role) }}</div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $manager->managed_accounts_count > 0 ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $manager->managed_accounts_count }} {{ Str::plural('member', $manager->managed_accounts_count) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-gray-500">No account managers available.</div>
                @endforelse
            </div>
        </div>

        {{-- Members Table --}}
        <div class="rounded-xl border border-gray-200 bg-white" x-data="{ selectedIds: [], selectAll: false, showBulkModal: false, showAssignModal: false }">
            {{-- Filters --}}
            <div class="px-4 py-3 border-b border-gray-100">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search by email, name, company..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>
                    <select name="role" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All Roles</option>
                        <option value="advertiser" @selected($filters['role'] === 'advertiser')>Advertisers</option>
                        <option value="publisher" @selected($filters['role'] === 'publisher')>Publishers</option>
                    </select>
                    <select name="manager" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All Members</option>
                        <option value="unassigned" @selected($filters['manager'] === 'unassigned')>Unassigned Only</option>
                        @foreach($accountManagers as $am)
                            <option value="{{ $am->id }}" @selected($filters['manager'] == $am->id)>{{ $am->email }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
                    @if($filters['search'] || $filters['role'] || $filters['manager'])
                        <a href="{{ route('admin.account-managers') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Bulk Actions Bar --}}
            <div x-show="selectedIds.length > 0" x-transition class="px-4 py-2 bg-brand-50 border-b border-brand-100 flex items-center gap-3" style="display: none;">
                <span class="text-sm font-medium text-brand-700" x-text="selectedIds.length + ' selected'"></span>
                <button @click="showAssignModal = true" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">Assign Manager</button>
                <button @click="selectedIds = []; selectAll = false" class="text-xs text-gray-500 hover:text-gray-700">Clear Selection</button>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-4 py-3 text-left w-10">
                                <input type="checkbox" x-model="selectAll" @change="selectedIds = selectAll ? {{ $members->pluck('id')->toJson() }} : []" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            </th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Member</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Role</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Account Manager</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Joined</th>
                            <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($members as $member)
                            <tr class="hover:bg-gray-50/50" :class="{ 'bg-brand-50/50': selectedIds.includes({{ $member->id }}) }">
                                <td class="px-4 py-3">
                                    <input type="checkbox" value="{{ $member->id }}" x-model.number="selectedIds" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                </td>
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
                                <td class="px-4 py-3">
                                    @if($member->accountManager)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-[10px]">
                                                {{ strtoupper(substr($member->accountManager->email, 0, 2)) }}
                                            </div>
                                            <span class="text-gray-700 text-xs">{{ $member->accountManager->email }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $member->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($member->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $member->created_at->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ $member->role === 'advertiser' ? route('admin.advertisers', ['search' => $member->email]) : route('admin.publishers', ['search' => $member->email]) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            Manage
                                        </a>
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <button @click="open = !open" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $member->accountManager ? 'Change' : 'Assign' }}
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-64 rounded-lg border border-gray-200 bg-white shadow-lg z-10" style="display: none;">
                                            <div class="p-2 border-b border-gray-100">
                                                <div class="text-xs font-medium text-gray-500">Assign to:</div>
                                            </div>
                                            <div class="max-h-48 overflow-y-auto">
                                                @foreach($accountManagers as $am)
                                                    <form method="POST" action="{{ route('admin.account-managers.assign') }}" class="block">
                                                        @csrf
                                                        <input type="hidden" name="user_ids[]" value="{{ $member->id }}">
                                                        <input type="hidden" name="account_manager_id" value="{{ $am->id }}">
                                                        <button type="submit" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 flex items-center gap-2 {{ $member->account_manager_id === $am->id ? 'bg-brand-50 text-brand-700' : 'text-gray-700' }}">
                                                            <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center text-brand-700 font-semibold text-[10px]">
                                                                {{ strtoupper(substr($am->email, 0, 2)) }}
                                                            </div>
                                                            <span class="truncate">{{ $am->email }}</span>
                                                            @if($member->account_manager_id === $am->id)
                                                                <svg class="w-4 h-4 ml-auto text-brand-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                            @endif
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                            @if($member->accountManager)
                                                <div class="border-t border-gray-100 p-2">
                                                    <form method="POST" action="{{ route('admin.account-managers.assign') }}">
                                                        @csrf
                                                        <input type="hidden" name="user_ids[]" value="{{ $member->id }}">
                                                        <input type="hidden" name="account_manager_id" value="">
                                                        <button type="submit" class="w-full text-left px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 rounded-lg">Remove Assignment</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500">No members found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($members->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">{{ $members->links() }}</div>
            @endif

            {{-- Bulk Assign Modal --}}
            <div x-show="showAssignModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" style="display: none;">
                <div class="fixed inset-0 bg-black/50" @click="showAssignModal = false"></div>
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full mx-4" @click.stop>
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Assign Account Manager</h3>
                        <button @click="showAssignModal = false" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('admin.account-managers.assign') }}" class="p-6">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="user_ids[]" :value="id">
                        </template>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Account Manager</label>
                            <select name="account_manager_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                <option value="">Remove Assignment</option>
                                @foreach($accountManagers as $am)
                                    <option value="{{ $am->id }}">{{ $am->email }} ({{ $am->managed_accounts_count }} members)</option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">This will assign the selected account manager to <span x-text="selectedIds.length" class="font-semibold"></span> member(s).</p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showAssignModal = false" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Assign</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Assignment Section --}}
    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6" x-data="{ showBulk: false }">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Bulk Assignment</h3>
                <p class="text-xs text-gray-500 mt-0.5">Quickly assign all unassigned members or reassign groups</p>
            </div>
            <button @click="showBulk = !showBulk" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                <span x-text="showBulk ? 'Hide' : 'Show Options'"></span>
            </button>
        </div>
        <form x-show="showBulk" x-transition method="POST" action="{{ route('admin.account-managers.bulk-assign') }}" class="mt-4 pt-4 border-t border-gray-100 grid sm:grid-cols-4 gap-4" style="display: none;">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Filter by Role</label>
                <select name="filter_role" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All Roles</option>
                    <option value="advertiser">Advertisers Only</option>
                    <option value="publisher">Publishers Only</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Filter by Status</label>
                <select name="filter_status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="unassigned">Unassigned Only</option>
                    <option value="assigned">Assigned Only</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Assign To</label>
                <select name="account_manager_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Remove Assignment</option>
                    @foreach($accountManagers as $am)
                        <option value="{{ $am->id }}">{{ $am->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Apply Bulk Assignment</button>
            </div>
        </form>
    </div>
@endsection
