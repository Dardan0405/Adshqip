<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelpCenterController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $faqsQuery = Faq::query()
            ->published()
            ->byLanguage('en')
            ->ordered();

        if (! empty($filters['q'])) {
            $search = trim($filters['q']);
            $faqsQuery->where(function ($query) use ($search) {
                $query->where('question', 'like', '%' . $search . '%')
                    ->orWhere('answer', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['category'])) {
            $faqsQuery->where('category', $filters['category']);
        }

        $ticketsQuery = SupportTicket::query()
            ->withCount(['messages' => fn ($query) => $query->where('is_internal_note', false)])
            ->where('user_id', $request->user()->id)
            ->latest('updated_at');

        if (! empty($filters['status'])) {
            $ticketsQuery->where('status', $filters['status']);
        }

        return view('publisher.help-center.index', [
            'faqs' => $faqsQuery->limit(20)->get(),
            'faqCategories' => Faq::query()
                ->published()
                ->byLanguage('en')
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
            'tickets' => $ticketsQuery->paginate(10)->withQueryString(),
            'filters' => [
                'q' => $filters['q'] ?? '',
                'category' => $filters['category'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'summary' => [
                'open' => SupportTicket::query()
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', ['open', 'in_progress', 'waiting_reply'])
                    ->count(),
                'resolved' => SupportTicket::query()
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', ['resolved', 'closed'])
                    ->count(),
                'urgent' => SupportTicket::query()
                    ->where('user_id', $request->user()->id)
                    ->where('priority', 'urgent')
                    ->count(),
            ],
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

        DB::transaction(function () use ($request, $data) {
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
        });

        return redirect()
            ->route('publisher.help-center')
            ->with('success', 'Support ticket created.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $this->authorizeTicket($request, $ticket);

        $ticket->load([
            'assignedTo.userProfile',
            'messages' => fn ($query) => $query->where('is_internal_note', false)->with('sender.userProfile')->orderBy('created_at'),
        ]);

        return view('publisher.help-center.show', [
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
            ->route('publisher.help-center.tickets.show', $ticket)
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
            ->route('publisher.help-center.tickets.show', $ticket)
            ->with('success', 'Ticket closed.');
    }

    private function authorizeTicket(Request $request, SupportTicket $ticket): void
    {
        if ((int) $ticket->user_id !== (int) $request->user()->id) {
            abort(404);
        }
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
