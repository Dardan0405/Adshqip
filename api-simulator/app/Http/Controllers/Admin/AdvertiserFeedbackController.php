<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserFeedback;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvertiserFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $feedback = $this->baseQuery($filters)
            ->with('user.userProfile')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.feedback.index', [
            'feedback' => $feedback,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
                'rating' => $filters['rating'] ?? '',
            ],
            'summary' => $this->summary(),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function show(AdvertiserFeedback $feedback)
    {
        $feedback->load('user.userProfile');

        return view('admin.feedback.show', [
            'item' => $feedback,
            'types' => $this->types(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, AdvertiserFeedback $feedback)
    {
        $data = $request->validate([
            'status' => ['required', 'in:submitted,reviewed,planned,resolved,closed'],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $feedback->update([
            'status' => $data['status'],
            'admin_response' => $data['admin_response'] ?? null,
            'reviewed_at' => in_array($data['status'], ['reviewed', 'planned', 'resolved', 'closed'], true)
                ? ($feedback->reviewed_at ?: now())
                : $feedback->reviewed_at,
            'closed_at' => $data['status'] === 'closed' ? now() : $feedback->closed_at,
        ]);

        return redirect()
            ->route('admin.feedback.show', $feedback)
            ->with('success', 'Feedback updated successfully.');
    }

    public function createTestimonial(AdvertiserFeedback $feedback)
    {
        $feedback->load('user.userProfile');
        $profile = $feedback->user?->userProfile;
        $name = trim((string) (($profile?->first_name ?? '') . ' ' . ($profile?->last_name ?? ''))) ?: ($feedback->user?->email ?? 'Advertiser');

        Testimonial::create([
            'author_name' => $name,
            'author_title' => 'Advertiser',
            'author_company' => $profile?->company_name,
            'author_avatar_url' => $profile?->avatar_url,
            'quote' => $feedback->message,
            'rating' => $feedback->rating ?: 5,
            'is_featured' => false,
            'is_published' => false,
            'sort_order' => 0,
        ]);

        $feedback->update([
            'status' => $feedback->status === 'submitted' ? 'reviewed' : $feedback->status,
            'reviewed_at' => $feedback->reviewed_at ?: now(),
        ]);

        return redirect()
            ->route('admin.testimonials')
            ->with('success', 'Draft testimonial created from advertiser feedback.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $rows = $this->baseQuery($filters)
            ->with('user.userProfile')
            ->latest('updated_at')
            ->get();

        $filename = 'admin_advertiser_feedback_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Advertiser Email', 'Company', 'Type', 'Rating', 'Subject', 'Status', 'Page URL', 'Created At', 'Updated At']);

            foreach ($rows as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->user?->email,
                    $item->user?->userProfile?->company_name,
                    $item->type,
                    $item->rating,
                    $item->subject,
                    $item->status,
                    $item->page_url,
                    $item->created_at?->format('Y-m-d H:i:s'),
                    $item->updated_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function baseQuery(array $filters)
    {
        return AdvertiserFeedback::query()
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('subject', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'))
                        ->orWhereHas('user.userProfile', fn ($profileQuery) => $profileQuery->where('company_name', 'like', '%' . $search . '%'))
                        ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search));
                });
            })
            ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['rating']), fn ($query) => $query->where('rating', $filters['rating']));
    }

    private function summary(): array
    {
        return [
            'total' => AdvertiserFeedback::count(),
            'submitted' => AdvertiserFeedback::where('status', 'submitted')->count(),
            'planned' => AdvertiserFeedback::where('status', 'planned')->count(),
            'resolved' => AdvertiserFeedback::where('status', 'resolved')->count(),
        ];
    }

    private function types(): array
    {
        return [
            'bug' => 'Bug',
            'feature_request' => 'Feature Request',
            'improvement' => 'Improvement',
            'general' => 'General',
        ];
    }

    private function statuses(): array
    {
        return [
            'submitted' => 'Submitted',
            'reviewed' => 'Reviewed',
            'planned' => 'Planned',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }
}
