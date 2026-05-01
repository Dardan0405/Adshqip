@php($isEdit = $mode === 'edit')
<div x-show="{{ $showProperty }}" class="fixed inset-0 z-50 overflow-y-auto" style="display:none">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="fixed inset-0 bg-black/50" @click="{{ $showProperty }} = false"></div>
        <div class="relative w-full max-w-3xl rounded-xl bg-white p-6 shadow-xl" @click.stop>
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">{{ $isEdit ? 'Edit Fraud Rule' : 'Create Fraud Rule' }}</h3>
                <button type="button" @click="{{ $showProperty }} = false" class="rounded p-1 text-gray-400 hover:bg-gray-100">X</button>
            </div>
            <form method="POST" @if($isEdit) :action="'/admin/fraud-rules/' + editItem.id" @else action="{{ $action }}" @endif class="space-y-4">
                @csrf
                @if($method !== 'POST') @method($method) @endif
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Rule Name</label>
                        <input name="rule_name" required @if($isEdit) x-model="editItem.rule_name" @else value="{{ old('rule_name') }}" @endif class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Rule Type</label>
                        <select name="rule_type" required @if($isEdit) x-model="editItem.rule_type" @endif class="w-full rounded-lg border px-3 py-2 text-sm">
                            @foreach($ruleTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Match Value</label>
                    <textarea name="match_value" rows="4" @if($isEdit) x-model="editItem.match_value" @endif class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="One IP, country code, fingerprint, or user-agent keyword per line">{{ $isEdit ? '' : old('match_value') }}</textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Threshold</label>
                        <input type="number" min="1" name="threshold_value" @if($isEdit) x-model="editItem.threshold_value" @else value="{{ old('threshold_value') }}" @endif class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Window Seconds</label>
                        <input type="number" min="1" name="reset_period_seconds" @if($isEdit) x-model="editItem.reset_period_seconds" @else value="{{ old('reset_period_seconds') }}" @endif class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Action</label>
                        <select name="action" required @if($isEdit) x-model="editItem.action" @endif class="w-full rounded-lg border px-3 py-2 text-sm">
                            @foreach($actions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Severity</label>
                        <select name="severity" required @if($isEdit) x-model="editItem.severity" @endif class="w-full rounded-lg border px-3 py-2 text-sm">
                            @foreach($severities as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Description</label>
                    <textarea name="description" rows="3" @if($isEdit) x-model="editItem.description" @endif class="w-full rounded-lg border px-3 py-2 text-sm">{{ $isEdit ? '' : old('description') }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @if($isEdit) :checked="editItem.is_active" @else checked @endif class="rounded border-gray-300 text-brand-600">
                    Active
                </label>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" @click="{{ $showProperty }} = false" class="rounded-lg bg-gray-100 px-4 py-2 text-sm">Cancel</button>
                    <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white">{{ $isEdit ? 'Save Rule' : 'Create Rule' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
