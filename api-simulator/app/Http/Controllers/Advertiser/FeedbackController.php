<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserFeedback;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $feedback = $this->baseQuery($request, $filters)
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('advertiser.feedback.index', [
            'feedback' => $feedback,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'summary' => $this->summary($request),
            'types' => $this->types(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:bug,feature_request,improvement,general'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'page_url' => ['nullable', 'url', 'max:500'],
        ]);

        $item = AdvertiserFeedback::create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'rating' => $data['rating'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'page_url' => $data['page_url'] ?? null,
            'status' => 'submitted',
        ]);

        return redirect()
            ->route('advertiser.feedback.show', $item)
            ->with('success', 'Feedback submitted.');
    }

    public function show(Request $request, AdvertiserFeedback $feedback)
    {
        $this->authorizeFeedback($request, $feedback);

        return view('advertiser.feedback.show', [
            'item' => $feedback,
            'types' => $this->types(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, AdvertiserFeedback $feedback)
    {
        $this->authorizeFeedback($request, $feedback);

        if (! in_array($feedback->status, ['submitted', 'reviewed'], true)) {
            return back()->withErrors(['feedback' => 'Only open feedback can be edited.']);
        }

        $data = $request->validate([
            'type' => ['required', 'in:bug,feature_request,improvement,general'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'page_url' => ['nullable', 'url', 'max:500'],
        ]);

        $feedback->update($data);

        return redirect()
            ->route('advertiser.feedback.show', $feedback)
            ->with('success', 'Feedback updated.');
    }

    public function close(Request $request, AdvertiserFeedback $feedback)
    {
        $this->authorizeFeedback($request, $feedback);

        $feedback->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return redirect()
            ->route('advertiser.feedback.show', $feedback)
            ->with('success', 'Feedback closed.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $rows = $this->baseQuery($request, $filters)
            ->latest('updated_at')
            ->get();

        $filename = 'advertiser_feedback_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Type', 'Rating', 'Subject', 'Status', 'Page URL', 'Created At', 'Updated At', 'Closed At']);

            foreach ($rows as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->type,
                    $item->rating,
                    $item->subject,
                    $item->status,
                    $item->page_url,
                    $item->created_at?->format('Y-m-d H:i:s'),
                    $item->updated_at?->format('Y-m-d H:i:s'),
                    $item->closed_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function baseQuery(Request $request, array $filters)
    {
        return AdvertiserFeedback::query()
            ->where('user_id', $request->user()->id)
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('subject', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%')
                        ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search));
                });
            })
            ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']));
    }

    private function summary(Request $request): array
    {
        return [
            'total' => AdvertiserFeedback::where('user_id', $request->user()->id)->count(),
            'submitted' => AdvertiserFeedback::where('user_id', $request->user()->id)->where('status', 'submitted')->count(),
            'planned' => AdvertiserFeedback::where('user_id', $request->user()->id)->where('status', 'planned')->count(),
            'resolved' => AdvertiserFeedback::where('user_id', $request->user()->id)->where('status', 'resolved')->count(),
        ];
    }

    private function authorizeFeedback(Request $request, AdvertiserFeedback $feedback): void
    {
        if ((int) $feedback->user_id !== (int) $request->user()->id) {
            abort(404);
        }
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
