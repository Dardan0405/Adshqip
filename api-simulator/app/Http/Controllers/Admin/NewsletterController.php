<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $query = Newsletter::query()->with('user');

        if ($search = trim((string) $request->get('search'))) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        $subscribers = $query->orderByDesc('subscribed_at')->paginate(20)->withQueryString();

        return view('admin.newsletters.index', [
            'subscribers' => $subscribers,
            'summary' => [
                'total' => Newsletter::count(),
                'subscribed' => Newsletter::where('status', 'subscribed')->count(),
                'unsubscribed' => Newsletter::where('status', 'unsubscribed')->count(),
                'bounced' => Newsletter::where('status', 'bounced')->count(),
            ],
            'sources' => Newsletter::query()->select('source')->distinct()->orderBy('source')->pluck('source'),
        ]);
    }

    public function export(Request $request)
    {
        $query = Newsletter::query();

        if ($search = trim((string) $request->get('search'))) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        $rows = $query->orderByDesc('subscribed_at')->get();
        $filename = 'newsletter_subscribers_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Email', 'Source', 'Status', 'Subscribed At', 'Unsubscribed At']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->email,
                    $row->source,
                    $row->status,
                    optional($row->subscribed_at)->format('Y-m-d H:i:s'),
                    optional($row->unsubscribed_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function unsubscribe(Newsletter $newsletter)
    {
        $newsletter->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function resubscribe(Newsletter $newsletter)
    {
        $newsletter->update([
            'status' => 'subscribed',
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);

        return response()->json(['success' => true]);
    }
}
