<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserContact;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvertiserContactController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'advertiser_id' => ['nullable', 'integer', 'exists:aq_users,id'],
        ]);

        $contacts = $this->baseQuery($filters)
            ->with('user.userProfile')
            ->orderByDesc('is_primary')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
                'advertiser_id' => $filters['advertiser_id'] ?? '',
            ],
            'summary' => $this->summary(),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
            'advertisers' => $this->advertisers(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'advertiser_id' => ['nullable', 'integer', 'exists:aq_users,id'],
        ]);

        $contacts = $this->baseQuery($filters)
            ->with('user.userProfile')
            ->orderByDesc('is_primary')
            ->latest('updated_at')
            ->get();

        $filename = 'admin_advertiser_contacts_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Advertiser Email', 'Advertiser Company', 'Name', 'Email', 'Phone', 'Company', 'Job Title', 'Type', 'Status', 'Primary', 'Last Contacted', 'Created At', 'Updated At']);

            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->id,
                    $contact->user?->email,
                    $contact->user?->userProfile?->company_name,
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
                    $contact->updated_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function baseQuery(array $filters)
    {
        return AdvertiserContact::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('job_title', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'))
                        ->orWhereHas('user.userProfile', fn ($profileQuery) => $profileQuery->where('company_name', 'like', '%' . $search . '%'))
                        ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search));
                });
            })
            ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['advertiser_id']), fn ($query) => $query->where('user_id', $filters['advertiser_id']));
    }

    private function summary(): array
    {
        return [
            'total' => AdvertiserContact::count(),
            'active' => AdvertiserContact::where('status', 'active')->count(),
            'primary' => AdvertiserContact::where('is_primary', true)->count(),
            'recent' => AdvertiserContact::where('last_contacted_at', '>=', now()->subDays(30))->count(),
        ];
    }

    private function advertisers()
    {
        return User::query()
            ->where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();
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
