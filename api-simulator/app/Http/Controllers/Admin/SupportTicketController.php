<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $ticketsQuery = SupportTicket::query()
            ->with(['user.userProfile', 'assignedTo.userProfile'])
            ->withCount('messages');

        if ($search = trim((string) $request->input('search'))) {
            $ticketsQuery->where(function ($query) use ($search) {
                $query->where('subject', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
            });
        }

        if ($status = $request->input('status')) {
            $ticketsQuery->where('status', $status);
        }

        if ($priority = $request->input('priority')) {
            $ticketsQuery->where('priority', $priority);
        }

        if ($category = $request->input('category')) {
            $ticketsQuery->where('category', $category);
        }

        if ($assignedTo = $request->input('assigned_to')) {
            $ticketsQuery->where('assigned_to', $assignedTo);
        }

        $tickets = $ticketsQuery->latest('updated_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_reply'])->count(),
            'resolved' => SupportTicket::whereIn('status', ['resolved', 'closed'])->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')->count(),
        ];

        $assignableAdmins = User::query()
            ->whereIn('role', ['admin', 'manager', 'operational'])
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.support-tickets.index', compact('tickets', 'summary', 'assignableAdmins'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load([
            'user.userProfile',
            'assignedTo.userProfile',
            'messages.sender.userProfile',
        ]);

        $assignableAdmins = User::query()
            ->whereIn('role', ['admin', 'manager', 'operational'])
            ->where('is_deleted', false)
            ->with('userProfile')
            ->orderBy('email')
            ->get();

        return view('admin.support-tickets.show', compact('supportTicket', 'assignableAdmins'));
    }

    public function update(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:open,in_progress,waiting_reply,resolved,closed'],
            'assigned_to' => ['nullable', 'exists:aq_users,id'],
        ]);

        $supportTicket->fill($validated);
        $supportTicket->resolved_at = in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null;
        $supportTicket->save();

        return redirect()
            ->route('admin.support-tickets.show', $supportTicket)
            ->with('success', 'Support ticket updated successfully.');
    }

    public function reply(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        SupportMessage::create([
            'ticket_id' => $supportTicket->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'is_internal_note' => (bool) ($validated['is_internal_note'] ?? false),
        ]);

        if (! ((bool) ($validated['is_internal_note'] ?? false))) {
            $supportTicket->status = 'in_progress';
            $supportTicket->updated_at = now();
            $supportTicket->save();
        }

        return redirect()
            ->route('admin.support-tickets.show', $supportTicket)
            ->with('success', 'Support reply saved successfully.');
    }
}
