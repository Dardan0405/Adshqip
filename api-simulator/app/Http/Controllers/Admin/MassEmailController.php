<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MassEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MassEmailController extends Controller
{
    public function index()
    {
        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->with('profile')
            ->orderBy('email')
            ->get();

        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->with('profile')
            ->orderBy('email')
            ->get();

        $recentEmails = MassEmail::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.mass-email', [
            'advertisers' => $advertisers,
            'publishers' => $publishers,
            'recentEmails' => $recentEmails,
            'stats' => [
                'totalAdvertisers' => $advertisers->count(),
                'totalPublishers' => $publishers->count(),
                'totalEmailsSent' => MassEmail::where('status', 'completed')->sum('sent_count'),
                'recentCampaigns' => MassEmail::count(),
            ],
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => ['required', 'in:advertisers,publishers'],
            'recipient_scope' => ['required', 'in:all,selected'],
            'selected_recipients' => ['required_if:recipient_scope,selected', 'array'],
            'selected_recipients.*' => ['integer', 'exists:aq_users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:50000'],
        ]);

        $role = $validated['recipient_type'] === 'advertisers' ? 'advertiser' : 'publisher';

        if ($validated['recipient_scope'] === 'all') {
            $recipients = User::where('role', $role)
                ->where('is_deleted', false)
                ->where('status', 'active')
                ->get();
            $recipientIds = null;
        } else {
            $recipients = User::whereIn('id', $validated['selected_recipients'])
                ->where('role', $role)
                ->where('is_deleted', false)
                ->where('status', 'active')
                ->get();
            $recipientIds = $recipients->pluck('id')->toArray();
        }

        if ($recipients->isEmpty()) {
            return redirect()
                ->route('admin.mass-email')
                ->with('error', 'No valid recipients found for the selected criteria.');
        }

        $massEmail = MassEmail::create([
            'recipient_type' => $validated['recipient_type'],
            'recipient_scope' => $validated['recipient_scope'],
            'recipient_ids' => $recipientIds,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'recipients_count' => $recipients->count(),
            'sent_count' => 0,
            'failed_count' => 0,
            'status' => 'sending',
            'created_by' => $request->user()?->id,
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            try {
                Mail::raw($validated['message'], function ($mail) use ($recipient, $validated) {
                    $mail->to($recipient->email)
                        ->subject($validated['subject']);
                });
                $sentCount++;
            } catch (\Exception $e) {
                Log::error('Mass email failed for recipient', [
                    'mass_email_id' => $massEmail->id,
                    'recipient_id' => $recipient->id,
                    'recipient_email' => $recipient->email,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $massEmail->update([
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'status' => $failedCount === $recipients->count() ? 'failed' : 'completed',
        ]);

        $message = "Email campaign completed: {$sentCount} sent";
        if ($failedCount > 0) {
            $message .= ", {$failedCount} failed";
        }

        return redirect()
            ->route('admin.mass-email')
            ->with('success', $message);
    }

    public function history()
    {
        $emails = MassEmail::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.mass-email-history', [
            'emails' => $emails,
        ]);
    }
}
