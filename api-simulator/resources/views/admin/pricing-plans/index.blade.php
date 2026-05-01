@extends('layouts.admin')

@section('title', 'Pricing Plans')

@section('content')
    @if(session('success'))
        <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pricing Plans</h1>
            <p class="mt-1 text-sm text-gray-500">Manage the plans shown on the public pricing section and keep one pricing source of truth.</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-500">Total Plans</div>
            <div class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-500">Active</div>
            <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-amber-500">Popular</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($stats['popular']) }}</div>
        </div>
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4">
            <div class="text-[10px] font-semibold uppercase tracking-wider text-purple-500">Enterprise</div>
            <div class="mt-2 text-2xl font-bold text-purple-700">{{ number_format($stats['enterprise']) }}</div>
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white">
        <form method="POST" action="{{ route('admin.pricing-plans.store') }}" class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="cpc-growth" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="CPC Growth" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Target Audience</label>
                <select name="target_audience" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="both">Both</option>
                    <option value="advertiser">Advertiser</option>
                    <option value="publisher">Publisher</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Currency</label>
                <input type="text" name="currency" value="{{ old('currency', 'USD') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm uppercase focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" maxlength="3" required>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Monthly Price</label>
                <input type="number" step="0.01" min="0" name="price_monthly" value="{{ old('price_monthly') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="0.20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Yearly Price</label>
                <input type="number" step="0.01" min="0" name="price_yearly" value="{{ old('price_yearly') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="2.00">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Impressions Limit</label>
                <input type="number" min="0" name="impressions_limit" value="{{ old('impressions_limit') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="100000">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ old('description') }}</textarea>
            </div>
            <div class="md:col-span-2 xl:col-span-4">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Features</label>
                <textarea name="features_input" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="One feature per line" required>{{ old('features_input') }}</textarea>
            </div>
            <div class="xl:col-span-4 flex flex-wrap items-center gap-3">
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                    <input type="checkbox" name="is_popular" value="1" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500" {{ old('is_popular') ? 'checked' : '' }}>
                    Mark as Popular
                </label>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-purple-200 bg-purple-50 px-4 py-2 text-sm font-semibold text-purple-700 hover:bg-purple-100">
                    <input type="checkbox" name="is_enterprise" value="1" class="rounded border-purple-300 text-purple-600 focus:ring-purple-500" {{ old('is_enterprise') ? 'checked' : '' }}>
                    Mark as Enterprise
                </label>
                <button type="submit" class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Save Plan</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Plan</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Audience</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Pricing</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-4 py-3 font-medium text-gray-900">#{{ $plan->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $plan->name }}</div>
                                <div class="text-xs text-gray-400">{{ $plan->slug }}</div>
                                @if($plan->features)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach(array_slice($plan->features, 0, 3) as $feature)
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600">{{ $feature }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 capitalize">{{ str_replace('_', ' ', $plan->target_audience) }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                <div>{{ $plan->currency }} {{ $plan->price_monthly !== null ? number_format((float) $plan->price_monthly, 2) : 'Custom' }} / month</div>
                                <div class="text-xs text-gray-400">{{ $plan->price_yearly !== null ? $plan->currency . ' ' . number_format((float) $plan->price_yearly, 2) . ' / year' : 'No yearly price' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $plan->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $plan->status }}</span>
                                    @if($plan->is_popular)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-amber-700">Popular</span>
                                    @endif
                                    @if($plan->is_enterprise)
                                        <span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-purple-700">Enterprise</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="edit-plan-btn rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                                        data-id="{{ $plan->id }}"
                                        data-slug="{{ $plan->slug }}"
                                        data-name="{{ $plan->name }}"
                                        data-description="{{ $plan->description }}"
                                        data-target-audience="{{ $plan->target_audience }}"
                                        data-price-monthly="{{ $plan->price_monthly }}"
                                        data-price-yearly="{{ $plan->price_yearly }}"
                                        data-currency="{{ $plan->currency }}"
                                        data-impressions-limit="{{ $plan->impressions_limit }}"
                                        data-sort-order="{{ $plan->sort_order }}"
                                        data-is-popular="{{ $plan->is_popular ? '1' : '0' }}"
                                        data-is-enterprise="{{ $plan->is_enterprise ? '1' : '0' }}"
                                        data-features-b64="{{ base64_encode(implode("\n", $plan->features ?? [])) }}">
                                        Edit
                                    </button>
                                    @if($plan->status === 'active')
                                        <form method="POST" action="{{ route('admin.pricing-plans.block', $plan) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50">Block</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.pricing-plans.unblock', $plan) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">Unblock</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none"><path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0H5m14 0h2m-16 0H3m2 0h14M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5m-4 0h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    <p class="text-sm text-gray-500">No pricing plans found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $plans->links() }}</div>
        @endif
    </div>

    <div id="editPlanModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4">
        <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Edit Pricing Plan</h3>
                    <p class="text-sm text-gray-500">Update the plan that feeds the public pricing section.</p>
                </div>
                <button type="button" onclick="closeEditPlan()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </button>
            </div>
            <form id="editPlanForm" method="POST" class="grid gap-4 p-6 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Slug</label>
                    <input id="edit_slug" type="text" name="slug" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Name</label>
                    <input id="edit_name" type="text" name="name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Target Audience</label>
                    <select id="edit_target_audience" name="target_audience" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                        <option value="both">Both</option>
                        <option value="advertiser">Advertiser</option>
                        <option value="publisher">Publisher</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Currency</label>
                    <input id="edit_currency" type="text" name="currency" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm uppercase" maxlength="3" required>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Monthly Price</label>
                    <input id="edit_price_monthly" type="number" step="0.01" min="0" name="price_monthly" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Yearly Price</label>
                    <input id="edit_price_yearly" type="number" step="0.01" min="0" name="price_yearly" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Impressions Limit</label>
                    <input id="edit_impressions_limit" type="number" min="0" name="impressions_limit" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Sort Order</label>
                    <input id="edit_sort_order" type="number" min="0" name="sort_order" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="edit_description" name="description" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Features</label>
                    <textarea id="edit_features_input" name="features_input" rows="5" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" required></textarea>
                </div>
                <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-100">
                        <input id="edit_is_popular" type="checkbox" name="is_popular" value="1" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                        Mark as Popular
                    </label>
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-purple-200 bg-purple-50 px-4 py-2 text-sm font-semibold text-purple-700 hover:bg-purple-100">
                        <input id="edit_is_enterprise" type="checkbox" name="is_enterprise" value="1" class="rounded border-purple-300 text-purple-600 focus:ring-purple-500">
                        Mark as Enterprise
                    </label>
                    <div class="ml-auto flex gap-3">
                        <button type="button" onclick="closeEditPlan()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update Plan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editPlanModal');
    const form = document.getElementById('editPlanForm');

    document.querySelectorAll('.edit-plan-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            form.action = '{{ url("admin/pricing-plans") }}/' + this.dataset.id;
            document.getElementById('edit_slug').value = this.dataset.slug;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_description').value = this.dataset.description || '';
            document.getElementById('edit_target_audience').value = this.dataset.targetAudience;
            document.getElementById('edit_price_monthly').value = this.dataset.priceMonthly;
            document.getElementById('edit_price_yearly').value = this.dataset.priceYearly;
            document.getElementById('edit_currency').value = this.dataset.currency;
            document.getElementById('edit_impressions_limit').value = this.dataset.impressionsLimit;
            document.getElementById('edit_sort_order').value = this.dataset.sortOrder;
            document.getElementById('edit_is_popular').checked = this.dataset.isPopular === '1';
            document.getElementById('edit_is_enterprise').checked = this.dataset.isEnterprise === '1';
            document.getElementById('edit_features_input').value = this.dataset.featuresB64 ? atob(this.dataset.featuresB64) : '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
});

function closeEditPlan() {
    const modal = document.getElementById('editPlanModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush
