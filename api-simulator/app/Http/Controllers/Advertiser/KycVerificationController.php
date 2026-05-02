<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\KycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KycVerificationController extends Controller
{
    public function index(Request $request)
    {
        $current = KycVerification::query()
            ->with(['documents', 'reviewer'])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        return view('advertiser.kyc-verification.index', [
            'verification' => $current,
            'history' => KycVerification::query()
                ->withCount('documents')
                ->where('user_id', $request->user()->id)
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
            'levels' => $this->verificationLevels(),
            'statuses' => $this->verificationStatuses(),
            'idTypes' => $this->idTypes(),
            'businessTypes' => $this->businessTypes(),
            'documentTypes' => $this->documentTypes(),
            'countries' => $this->countries(),
            'stats' => [
                'total' => KycVerification::where('user_id', $request->user()->id)->count(),
                'pending' => KycVerification::where('user_id', $request->user()->id)->whereIn('status', ['pending', 'in_review'])->count(),
                'approved' => KycVerification::where('user_id', $request->user()->id)->where('status', 'approved')->count(),
                'rejected' => KycVerification::where('user_id', $request->user()->id)->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->baseRules());
        $payload = $this->normalizePayload($validated);

        DB::transaction(function () use ($request, $payload) {
            $verification = KycVerification::create($payload + [
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $this->storeDocuments($request, $verification);
            $this->syncUserKyc($request, $verification);
        });

        return redirect()
            ->route('advertiser.kyc-verification')
            ->with('success', 'KYC verification submitted successfully.');
    }

    public function update(Request $request, KycVerification $verification)
    {
        $this->authorizeVerification($request, $verification);

        if ($verification->status === 'approved') {
            return back()->withErrors(['verification' => 'Approved KYC records cannot be edited.']);
        }

        $validated = $request->validate($this->baseRules());
        $payload = $this->normalizePayload($validated);

        DB::transaction(function () use ($request, $verification, $payload) {
            $nextStatus = in_array($verification->status, ['pending', 'in_review'], true) ? 'in_review' : 'pending';

            $verification->update($payload + [
                'status' => $nextStatus,
                'submitted_at' => $verification->submitted_at ?: now(),
            ]);

            $this->storeDocuments($request, $verification);
            $this->syncUserKyc($request, $verification->fresh());
        });

        return redirect()
            ->route('advertiser.kyc-verification')
            ->with('success', 'KYC verification updated successfully.');
    }

    private function authorizeVerification(Request $request, KycVerification $verification): void
    {
        if ((int) $verification->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    private function baseRules(): array
    {
        $documentTypeKeys = implode(',', array_keys($this->documentTypes()));

        return [
            'verification_level' => ['required', 'in:' . implode(',', array_keys($this->verificationLevels()))],
            'legal_first_name' => ['nullable', 'string', 'max:100'],
            'legal_last_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'size:2'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'id_type' => ['nullable', 'in:' . implode(',', array_keys($this->idTypes()))],
            'id_issuing_country' => ['nullable', 'string', 'size:2'],
            'id_expiry_date' => ['nullable', 'date'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_registration_number' => ['nullable', 'string', 'max:100'],
            'business_type' => ['nullable', 'in:' . implode(',', array_keys($this->businessTypes()))],
            'business_country' => ['nullable', 'string', 'size:2'],
            'business_address' => ['nullable', 'string'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'risk_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'risk_flags_input' => ['nullable', 'string'],
            'aml_check_passed' => ['nullable', 'boolean'],
            'sanctions_check_passed' => ['nullable', 'boolean'],
            'document_types' => ['nullable', 'array'],
            'document_types.*' => ['nullable', 'in:' . $documentTypeKeys],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }

    private function normalizePayload(array $validated): array
    {
        return [
            'verification_level' => $validated['verification_level'],
            'legal_first_name' => $validated['legal_first_name'] ?? null,
            'legal_last_name' => $validated['legal_last_name'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'nationality' => ! empty($validated['nationality']) ? strtoupper($validated['nationality']) : null,
            'id_number' => $validated['id_number'] ?? null,
            'id_type' => $validated['id_type'] ?? null,
            'id_issuing_country' => ! empty($validated['id_issuing_country']) ? strtoupper($validated['id_issuing_country']) : null,
            'id_expiry_date' => $validated['id_expiry_date'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'business_registration_number' => $validated['business_registration_number'] ?? null,
            'business_type' => $validated['business_type'] ?? null,
            'business_country' => ! empty($validated['business_country']) ? strtoupper($validated['business_country']) : null,
            'business_address' => $validated['business_address'] ?? null,
            'vat_number' => $validated['vat_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'risk_score' => $validated['risk_score'] ?? null,
            'risk_flags' => collect(preg_split('/[\r\n,]+/', (string) ($validated['risk_flags_input'] ?? '')))
                ->map(fn ($flag) => trim((string) $flag))
                ->filter()
                ->values()
                ->all() ?: null,
            'aml_check_passed' => array_key_exists('aml_check_passed', $validated) ? (bool) $validated['aml_check_passed'] : null,
            'sanctions_check_passed' => array_key_exists('sanctions_check_passed', $validated) ? (bool) $validated['sanctions_check_passed'] : null,
        ];
    }

    private function storeDocuments(Request $request, KycVerification $verification): void
    {
        $types = $request->input('document_types', []);
        $files = $request->file('documents', []);

        foreach ($types as $index => $type) {
            $file = $files[$index] ?? null;
            if (! $type || ! $file) {
                continue;
            }

            $existing = $verification->documents()->where('document_type', $type)->first();
            if ($existing && $existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }

            $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
            $storedPath = $file->storeAs(
                'kyc-documents/' . $verification->user_id . '/' . $verification->id,
                Str::slug($type) . '-' . now()->timestamp . '-' . Str::random(8) . '.' . $extension,
                'public'
            );

            $document = $existing ?: new KycDocument([
                'kyc_id' => $verification->id,
                'user_id' => $verification->user_id,
                'document_type' => $type,
            ]);

            $document->fill([
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'status' => 'uploaded',
                'rejection_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            $document->save();
        }
    }

    private function syncUserKyc(Request $request, KycVerification $verification): void
    {
        $user = $request->user();
        $attributes = ['kyc_status' => $verification->status];

        if ($verification->status === 'approved') {
            $attributes['kyc_level'] = $verification->verification_level;
            $attributes['kyc_verified_at'] = $verification->approved_at ?? now();
        } elseif (! $user->kyc_verified_at) {
            $attributes['kyc_level'] = 'none';
            $attributes['kyc_verified_at'] = null;
        }

        $user->forceFill($attributes)->save();
    }

    private function verificationLevels(): array
    {
        return [
            'basic' => 'Basic',
            'standard' => 'Standard',
            'enhanced' => 'Enhanced',
        ];
    }

    private function verificationStatuses(): array
    {
        return [
            'not_started' => 'Not Started',
            'pending' => 'Pending',
            'in_review' => 'In Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
        ];
    }

    private function idTypes(): array
    {
        return [
            'passport' => 'Passport',
            'national_id' => 'National ID',
            'drivers_license' => 'Driver License',
            'residence_permit' => 'Residence Permit',
        ];
    }

    private function businessTypes(): array
    {
        return [
            'individual' => 'Individual',
            'sole_proprietor' => 'Sole Proprietor',
            'llc' => 'LLC',
            'corporation' => 'Corporation',
            'partnership' => 'Partnership',
            'non_profit' => 'Non Profit',
        ];
    }

    private function documentTypes(): array
    {
        return [
            'id_front' => 'ID Front',
            'id_back' => 'ID Back',
            'passport' => 'Passport',
            'selfie' => 'Selfie',
            'selfie_with_id' => 'Selfie With ID',
            'proof_of_address' => 'Proof Of Address',
            'business_registration' => 'Business Registration',
            'tax_certificate' => 'Tax Certificate',
            'bank_statement' => 'Bank Statement',
            'other' => 'Other',
        ];
    }

    private function countries(): array
    {
        return [
            'AL' => 'Albania',
            'XK' => 'Kosovo',
            'MK' => 'North Macedonia',
            'ME' => 'Montenegro',
            'RS' => 'Serbia',
            'BA' => 'Bosnia and Herzegovina',
            'HR' => 'Croatia',
            'SI' => 'Slovenia',
            'BG' => 'Bulgaria',
            'GR' => 'Greece',
            'RO' => 'Romania',
            'TR' => 'Turkey',
            'IT' => 'Italy',
            'DE' => 'Germany',
            'CH' => 'Switzerland',
            'GB' => 'United Kingdom',
            'US' => 'United States',
        ];
    }
}
