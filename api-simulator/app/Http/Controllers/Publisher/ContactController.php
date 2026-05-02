<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserContact;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $contacts = $this->baseQuery($request, $filters)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('publisher.contacts.index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'summary' => $this->summary($request),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
            'platformContacts' => $this->platformContacts($request),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateContact($request);
        $data['user_id'] = $request->user()->id;
        $data['is_primary'] = $request->boolean('is_primary');

        AdvertiserContact::create($data);

        return redirect()->route('publisher.contacts')->with('success', 'Contact created.');
    }

    public function update(Request $request, AdvertiserContact $contact)
    {
        $this->authorizeContact($request, $contact);

        $data = $this->validateContact($request);
        $data['is_primary'] = $request->boolean('is_primary');

        $contact->update($data);

        return redirect()->route('publisher.contacts', $request->only(['search', 'type', 'status']))->with('success', 'Contact updated.');
    }

    public function touch(Request $request, AdvertiserContact $contact)
    {
        $this->authorizeContact($request, $contact);

        $contact->update(['last_contacted_at' => now()]);

        return back()->with('success', 'Last contacted date updated.');
    }

    public function destroy(Request $request, AdvertiserContact $contact)
    {
        $this->authorizeContact($request, $contact);

        $contact->delete();

        return redirect()->route('publisher.contacts')->with('success', 'Contact deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $contacts = $this->baseQuery($request, $filters)
            ->orderByDesc('is_primary')
            ->orderBy('name')
            ->get();

        $filename = 'publisher_contacts_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Company', 'Job Title', 'Type', 'Status', 'Primary', 'Last Contacted', 'Created At']);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->id,
                    $contact->name,
                    $contact->email,
                    $contact->phone,
                    $contact->company,
                    $contact->job_title,
                    $contact->type,
                    $contact->status,
                    $contact->is_primary ? 'yes' : 'no',
                    $contact->last_contacted_at?->format('Y-m-d H:i:s'),
                    $contact->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateContact(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:180'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'type' => ['required', 'in:client,partner,agency,billing,technical,other'],
            'status' => ['required', 'in:active,inactive,archived'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function baseQuery(Request $request, array $filters)
    {
        return AdvertiserContact::query()
            ->where('user_id', $request->user()->id)
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('job_title', 'like', '%' . $search . '%')
                        ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search));
                });
            })
            ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']));
    }

    private function authorizeContact(Request $request, AdvertiserContact $contact): void
    {
        if ((int) $contact->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    private function summary(Request $request): array
    {
        return [
            'total' => AdvertiserContact::where('user_id', $request->user()->id)->count(),
            'active' => AdvertiserContact::where('user_id', $request->user()->id)->where('status', 'active')->count(),
            'primary' => AdvertiserContact::where('user_id', $request->user()->id)->where('is_primary', true)->count(),
            'recent' => AdvertiserContact::where('user_id', $request->user()->id)->where('last_contacted_at', '>=', now()->subDays(30))->count(),
        ];
    }

    private function platformContacts(Request $request): array
    {
        $accountManager = $request->user()->accountManager?->loadMissing('userProfile');
        $managerProfile = $accountManager?->userProfile;

        return [
            [
                'label' => 'Account Manager',
                'name' => $accountManager ? trim(($managerProfile?->first_name ?? '') . ' ' . ($managerProfile?->last_name ?? '')) ?: 'Assigned Manager' : 'Not assigned',
                'email' => $accountManager?->email,
                'phone' => $managerProfile?->phone ?: $managerProfile?->mobile_number,
                'description' => 'Campaign strategy, account growth, and onboarding help.',
            ],
            [
                'label' => 'Support Desk',
                'name' => 'Adshqip Support',
                'email' => config('mail.from.address'),
                'phone' => null,
                'description' => 'Technical issues, ticket follow-up, and platform access.',
            ],
            [
                'label' => 'Billing Team',
                'name' => 'Billing Support',
                'email' => config('mail.from.address'),
                'phone' => null,
                'description' => 'Invoices, deposits, subscriptions, and payout questions.',
            ],
        ];
    }

    private function types(): array
    {
        return [
            'client' => 'Client',
            'partner' => 'Partner',
            'agency' => 'Agency',
            'billing' => 'Billing',
            'technical' => 'Technical',
            'other' => 'Other',
        ];
    }

    private function statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'archived' => 'Archived',
        ];
    }
}
