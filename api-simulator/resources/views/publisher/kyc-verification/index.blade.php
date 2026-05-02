@extends('layouts.publisher')

@section('title', 'KYC Verification')

@section('content')
@php
    $verificationData = $verification ? $verification->toArray() : [];
    $statusClasses = [
        'not_started' => 'bg-gray-100 text-gray-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'in_review' => 'bg-blue-100 text-blue-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-rose-100 text-rose-700',
        'expired' => 'bg-gray-100 text-gray-700',
    ];
    $documentStatusClasses = [
        'uploaded' => 'bg-amber-100 text-amber-700',
        'verified' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-rose-100 text-rose-700',
    ];
    $isEdit = isset($verification);
    $riskFlagsValue = old('risk_flags_input', is_array(data_get($verificationData, 'risk_flags')) ? implode(', ', data_get($verificationData, 'risk_flags', [])) : '');
    $verificationLevel = old('verification_level', data_get($verificationData, 'verification_level', 'basic'));
    $legalFirstName = old('legal_first_name', data_get($verificationData, 'legal_first_name', ''));
    $legalLastName = old('legal_last_name', data_get($verificationData, 'legal_last_name', ''));
    $dateOfBirth = old('date_of_birth', data_get($verificationData, 'date_of_birth'));
    $nationality = old('nationality', data_get($verificationData, 'nationality', ''));
    $idNumber = old('id_number', data_get($verificationData, 'id_number', ''));
    $idType = old('id_type', data_get($verificationData, 'id_type', ''));
    $idIssuingCountry = old('id_issuing_country', data_get($verificationData, 'id_issuing_country', ''));
    $idExpiryDate = old('id_expiry_date', data_get($verificationData, 'id_expiry_date'));
    $businessName = old('business_name', data_get($verificationData, 'business_name', ''));
    $businessRegistrationNumber = old('business_registration_number', data_get($verificationData, 'business_registration_number', ''));
    $businessType = old('business_type', data_get($verificationData, 'business_type', ''));
    $businessCountry = old('business_country', data_get($verificationData, 'business_country', ''));
    $businessAddress = old('business_address', data_get($verificationData, 'business_address', ''));
    $vatNumber = old('vat_number', data_get($verificationData, 'vat_number', ''));
    $riskScore = old('risk_score', data_get($verificationData, 'risk_score', ''));
    $amlPassed = old('aml_check_passed', data_get($verificationData, 'aml_check_passed', false));
    $sanctionsPassed = old('sanctions_check_passed', data_get($verificationData, 'sanctions_check_passed', false));
    $notes = old('notes', data_get($verificationData, 'notes', ''));
    $currentStatus = data_get($verificationData, 'status', 'not_started');
    $currentLevel = data_get($verificationData, 'verification_level', 'basic');
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">KYC Verification</h1>
            <p class="mt-1 text-sm text-gray-500">Submit and track your publisher identity and business verification.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Pending</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Approved</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['approved']) }}</p>
        </div>
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Rejected</p>
            <p class="mt-2 text-2xl font-bold text-rose-700">{{ number_format($stats['rejected']) }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr),380px]">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ $isEdit ? 'Update Verification' : 'Create Verification' }}</h2>
                <p class="mt-1 text-sm text-gray-500">Complete the form and upload your supporting documents.</p>
            </div>

            <form method="POST" action="{{ $isEdit ? route('publisher.kyc-verification.update', $verification) : route('publisher.kyc-verification.store') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    <div class="rounded-xl border border-gray-200 bg-white p-6">
                        <h3 class="text-base font-semibold text-gray-900">Verification Details</h3>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">User</label>
                                <input value="{{ auth()->user()->email }}" disabled class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Verification Level</label>
                                <select name="verification_level" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" required>
                                    @foreach($levels as $value => $label)
                                        <option value="{{ $value }}" {{ $verificationLevel === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Legal First Name</label>
                                <input type="text" name="legal_first_name" value="{{ $legalFirstName }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Legal Last Name</label>
                                <input type="text" name="legal_last_name" value="{{ $legalLastName }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Date Of Birth</label>
                                <input type="date" name="date_of_birth" value="{{ $dateOfBirth ? \Illuminate\Support\Carbon::parse($dateOfBirth)->format('Y-m-d') : '' }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Nationality</label>
                                <select name="nationality" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                    <option value="">Select country...</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}" {{ $nationality === $code ? 'selected' : '' }}>{{ $name }}</option>
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
                                <input type="text" name="id_number" value="{{ $idNumber }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">ID Type</label>
                                <select name="id_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                    <option value="">Select type...</option>
                                    @foreach($idTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $idType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Issuing Country</label>
                                <select name="id_issuing_country" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                    <option value="">Select country...</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}" {{ $idIssuingCountry === $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Expiry Date</label>
                                <input type="date" name="id_expiry_date" value="{{ $idExpiryDate ? \Illuminate\Support\Carbon::parse($idExpiryDate)->format('Y-m-d') : '' }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-6">
                        <h3 class="text-base font-semibold text-gray-900">Business KYC</h3>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Name</label>
                                <input type="text" name="business_name" value="{{ $businessName }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Registration Number</label>
                                <input type="text" name="business_registration_number" value="{{ $businessRegistrationNumber }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Type</label>
                                <select name="business_type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                    <option value="">Select business type...</option>
                                    @foreach($businessTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $businessType === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Country</label>
                                <select name="business_country" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                                    <option value="">Select country...</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}" {{ $businessCountry === $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Business Address</label>
                                <textarea name="business_address" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ $businessAddress }}</textarea>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">VAT Number</label>
                                <input type="text" name="vat_number" value="{{ $vatNumber }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-6">
                        <h3 class="text-base font-semibold text-gray-900">Risk & Compliance</h3>
                        <div class="mt-5 grid gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Risk Score</label>
                                <input type="number" step="0.01" min="0" max="100" name="risk_score" value="{{ $riskScore }}" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Risk Flags</label>
                                <input type="text" name="risk_flags_input" value="{{ $riskFlagsValue }}" placeholder="pep, sanctions_match, high_risk_country" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="aml_check_passed" value="1" {{ $amlPassed ? 'checked' : '' }}>
                                    AML Check Passed
                                </label>
                            </div>
                            <div class="flex items-center gap-4">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="sanctions_check_passed" value="1" {{ $sanctionsPassed ? 'checked' : '' }}>
                                    Sanctions Check Passed
                                </label>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">Internal Notes</label>
                                <textarea name="notes" rows="4" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">{{ $notes }}</textarea>
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
                                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $documentStatusClasses[$document->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $document->status }}</span>
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
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
                    <button type="reset" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Reset</button>
                    <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ $isEdit ? 'Update KYC' : 'Submit KYC' }}</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Current Status</h2>
                </div>
                <div class="space-y-4 p-6 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-500">Status</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$currentStatus] ?? 'bg-gray-100 text-gray-700' }}">{{ $statuses[$currentStatus] ?? 'Not Started' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-500">Level</span>
                        <span class="font-semibold text-gray-900">{{ $levels[$currentLevel] ?? 'Basic' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-500">Submitted</span>
                        <span class="font-semibold text-gray-900">{{ $verification?->submitted_at?->format('M d, Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-500">Reviewed</span>
                        <span class="font-semibold text-gray-900">{{ $verification?->reviewed_at?->format('M d, Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-500">Approved</span>
                        <span class="font-semibold text-gray-900">{{ $verification?->approved_at?->format('M d, Y H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-gray-500">Expires</span>
                        <span class="font-semibold text-gray-900">{{ $verification?->expires_at?->format('M d, Y') ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">History</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($history as $item)
                        <div class="p-4 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-gray-900">#{{ $item->id }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $statusClasses[$item->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $statuses[$item->status] ?? $item->status }}</span>
                            </div>
                            <div class="mt-1 text-gray-500">{{ $levels[$item->verification_level] ?? ucfirst((string) $item->verification_level) }}</div>
                            <div class="mt-1 text-xs text-gray-400">{{ $item->documents_count }} documents • Submitted {{ $item->submitted_at?->format('M d, Y H:i') ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-500">No KYC records yet.</div>
                    @endforelse
                </div>
                @if($history->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">{{ $history->links() }}</div>
                @endif
            </div>
        </div>
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
@endsection
