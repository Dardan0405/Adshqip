@php
    $isEdit = isset($verification);
    $selectedUserId = old('user_id', $verification->user_id ?? request('user_id'));
    $riskFlagsValue = old('risk_flags_input', isset($verification) && is_array($verification->risk_flags) ? implode(', ', $verification->risk_flags) : '');
@endphp

<div class="space-y-6">
    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h3 class="text-base font-semibold text-gray-900">Verification Details</h3>
        <p class="mt-1 text-sm text-gray-500">Capture the user identity and business snapshot for the KYC review.</p>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">User</label>
                <select name="user_id" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                    <option value="">Select user...</option>
                    @foreach($users as $userOption)
                        <option value="{{ $userOption['id'] }}" {{ (string) $selectedUserId === (string) $userOption['id'] ? 'selected' : '' }}>
                            {{ $userOption['label'] }} • {{ $userOption['role'] }}
                        </option>
                    @endforeach
                </select>
                @error('user_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Verification Level</label>
                <select name="verification_level" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                    @foreach($levels as $value => $label)
                        <option value="{{ $value }}" {{ old('verification_level', $verification->verification_level ?? 'basic') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('verification_level') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Legal First Name</label>
                <input type="text" name="legal_first_name" value="{{ old('legal_first_name', $verification->legal_first_name ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Legal Last Name</label>
                <input type="text" name="legal_last_name" value="{{ old('legal_last_name', $verification->legal_last_name ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Date Of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($verification->date_of_birth ?? null)->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nationality</label>
                <select name="nationality" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Select country...</option>
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ old('nationality', $verification->nationality ?? '') === $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h3 class="text-base font-semibold text-gray-900">Identity Document</h3>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">ID Number</label>
                <input type="text" name="id_number" value="{{ old('id_number', $verification->id_number ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">ID Type</label>
                <select name="id_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Select type...</option>
                    @foreach($idTypes as $value => $label)
                        <option value="{{ $value }}" {{ old('id_type', $verification->id_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Issuing Country</label>
                <select name="id_issuing_country" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Select country...</option>
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ old('id_issuing_country', $verification->id_issuing_country ?? '') === $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Expiry Date</label>
                <input type="date" name="id_expiry_date" value="{{ old('id_expiry_date', optional($verification->id_expiry_date ?? null)->format('Y-m-d')) }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h3 class="text-base font-semibold text-gray-900">Business KYC</h3>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Name</label>
                <input type="text" name="business_name" value="{{ old('business_name', $verification->business_name ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Registration Number</label>
                <input type="text" name="business_registration_number" value="{{ old('business_registration_number', $verification->business_registration_number ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Type</label>
                <select name="business_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Select business type...</option>
                    @foreach($businessTypes as $value => $label)
                        <option value="{{ $value }}" {{ old('business_type', $verification->business_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Country</label>
                <select name="business_country" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Select country...</option>
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ old('business_country', $verification->business_country ?? '') === $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Address</label>
                <textarea name="business_address" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ old('business_address', $verification->business_address ?? '') }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">VAT Number</label>
                <input type="text" name="vat_number" value="{{ old('vat_number', $verification->vat_number ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h3 class="text-base font-semibold text-gray-900">Risk & Compliance</h3>
        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Risk Score</label>
                <input type="number" step="0.01" min="0" max="100" name="risk_score" value="{{ old('risk_score', $verification->risk_score ?? '') }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Risk Flags</label>
                <input type="text" name="risk_flags_input" value="{{ $riskFlagsValue }}" placeholder="pep, sanctions_match, high_risk_country" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            </div>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="aml_check_passed" value="1" {{ old('aml_check_passed', $verification->aml_check_passed ?? false) ? 'checked' : '' }}>
                    AML Check Passed
                </label>
            </div>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="sanctions_check_passed" value="1" {{ old('sanctions_check_passed', $verification->sanctions_check_passed ?? false) ? 'checked' : '' }}>
                    Sanctions Check Passed
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Internal Notes</label>
                <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ old('notes', $verification->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Documents</h3>
                <p class="mt-1 text-sm text-gray-500">Upload or replace KYC proof files. Accepted: JPG, PNG, PDF.</p>
            </div>
            <button type="button" onclick="addKycDocumentRow()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Add Document</button>
        </div>

        @if($isEdit && $verification->documents->isNotEmpty())
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                @foreach($verification->documents as $document)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $documentTypes[$document->document_type] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $document->file_name }}</div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $document->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($document->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $document->status }}</span>
                                    @if($document->verified_at)
                                        <span class="text-[10px] text-gray-400">Verified {{ $document->verified_at->format('M d, Y H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($document->file_path) }}" target="_blank" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-600 hover:bg-brand-50">Open</a>
                        </div>
                        @if($document->rejection_reason)
                            <p class="mt-3 text-xs text-rose-600">{{ $document->rejection_reason }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <div id="kycDocumentRows" class="mt-5 space-y-3">
            <div class="grid gap-3 md:grid-cols-[220px,1fr]">
                <select name="document_types[]" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <option value="">Choose document type...</option>
                    @foreach($documentTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="file" name="documents[]" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-600 hover:file:bg-brand-100">
            </div>
        </div>
        @error('documents.*') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
    </div>
</div>

@push('scripts')
<script>
function addKycDocumentRow() {
    const container = document.getElementById('kycDocumentRows');
    const row = document.createElement('div');
    row.className = 'grid gap-3 md:grid-cols-[220px,1fr,auto]';
    row.innerHTML = `
        <select name="document_types[]" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
            <option value="">Choose document type...</option>
            @foreach($documentTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <input type="file" name="documents[]" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-600 hover:file:bg-brand-100">
        <button type="button" onclick="this.parentElement.remove()" class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Remove</button>
    `;
    container.appendChild(row);
}
</script>
@endpush
