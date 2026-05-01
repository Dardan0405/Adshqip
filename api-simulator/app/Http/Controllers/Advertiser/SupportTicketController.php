<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $this->baseQuery($request, $filters)
            ->withCount(['messages' => fn ($query) => $query->where('is_internal_note', false)])
            ->latest('updated_at');

        return view('advertiser.support-tickets.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'priority' => $filters['priority'] ?? '',
                'category' => $filters['category'] ?? '',
            ],
            'summary' => $this->summary($request),
            'categories' => $this->categories(),
            'priorities' => $this->priorities(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:billing,technical,campaign,account,fraud,other'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = DB::transaction(function () use ($request, $data) {
            $ticket = SupportTicket::create([
                'user_id' => $request->user()->id,
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => $data['priority'],
                'status' => 'open',
            ]);

            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $request->user()->id,
                'message' => $data['message'],
                'is_internal_note' => false,
            ]);

            return $ticket;
        });

        return redirect()
            ->route('advertiser.support-tickets.show', $ticket)
            ->with('success', 'Support ticket created.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($request, $ticket);

        $ticket->load([
            'assignedTo.userProfile',
            'messages' => fn ($query) => $query
                ->where('is_internal_note', false)
                ->with('sender.userProfile')
                ->orderBy('created_at'),
        ]);

        return view('advertiser.support-tickets.show', [
            'ticket' => $ticket,
            'categories' => $this->categories(),
            'priorities' => $this->priorities(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($request, $ticket);

        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            return back()->withErrors(['ticket' => 'This ticket is closed. Create a new ticket if you still need help.']);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message' => $data['message'],
            'is_internal_note' => false,
        ]);

        $ticket->update([
            'status' => 'waiting_reply',
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('advertiser.support-tickets.show', $ticket)
            ->with('success', 'Reply sent.');
    }

    public function close(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($request, $ticket);

        $ticket->update([
            'status' => 'closed',
            'resolved_at' => now(),
        ]);

        return redirect()
            ->route('advertiser.support-tickets.show', $ticket)
            ->with('success', 'Ticket closed.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $tickets = $this->baseQuery($request, $filters)
            ->withCount(['messages' => fn ($query) => $query->where('is_internal_note', false)])
            ->latest('updated_at')
            ->get();

        $filename = 'advertiser_support_tickets_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Subject', 'Category', 'Priority', 'Status', 'Messages', 'Created At', 'Updated At', 'Resolved At']);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->id,
                    $ticket->subject,
                    $ticket->category,
                    $ticket->priority,
                    $ticket->status,
                    $ticket->messages_count,
                    $ticket->created_at?->format('Y-m-d H:i:s'),
                    $ticket->updated_at?->format('Y-m-d H:i:s'),
                    $ticket->resolved_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function baseQuery(Request $request, array $filters)
    {
        return SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($inner) use ($search) {
                    $inner->where('subject', 'like', '%' . $search . '%')
                        ->when(is_numeric($search), fn ($q) => $q->orWhere('id', (int) $search));
                });
            })
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['priority']), fn ($query) => $query->where('priority', $filters['priority']))
            ->when(! empty($filters['category']), fn ($query) => $query->where('category', $filters['category']));
    }

    private function authorizeTicket(Request $request, SupportTicket $ticket): void
    {
        if ((int) $ticket->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    private function summary(Request $request): array
    {
        return [
            'total' => SupportTicket::where('user_id', $request->user()->id)->count(),
            'open' => SupportTicket::where('user_id', $request->user()->id)->whereIn('status', ['open', 'in_progress', 'waiting_reply'])->count(),
            'resolved' => SupportTicket::where('user_id', $request->user()->id)->whereIn('status', ['resolved', 'closed'])->count(),
            'urgent' => SupportTicket::where('user_id', $request->user()->id)->where('priority', 'urgent')->count(),
        ];
    }

    private function categories(): array
    {
        return [
            'billing' => 'Billing',
            'technical' => 'Technical',
            'campaign' => 'Campaign',
            'account' => 'Account',
            'fraud' => 'Fraud',
            'other' => 'Other',
        ];
    }

    private function priorities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    private function statuses(): array
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'waiting_reply' => 'Waiting Reply',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];
    }
}
