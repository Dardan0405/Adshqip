<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = ActivityLog::query()
            ->with('user')
            ->where('user_id', $request->user()->id)
            ->latest('created_at');

        if ($search = trim((string) $request->get('search'))) {
            $baseQuery->where(function ($builder) use ($search) {
                $builder
                    ->where('action', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhere('browser', 'like', '%' . $search . '%')
                    ->orWhere('os', 'like', '%' . $search . '%')
                    ->orWhere('user_agent', 'like', '%' . $search . '%');
            });
        }

        if ($date = $request->get('date')) {
            $baseQuery->whereDate('created_at', $date);
        }

        if ($browser = $request->get('browser')) {
            $baseQuery->where('browser', $browser);
        }

        if ($os = $request->get('os')) {
            $baseQuery->where('os', $os);
        }

        $logs = $baseQuery->paginate(15)->withQueryString();
        $stats = ActivityLog::where('user_id', $request->user()->id);

        return view('advertiser.audit-logs.index', [
            'logs' => $logs,
            'totalLogs' => (clone $stats)->count(),
            'todayLogs' => (clone $stats)->whereDate('created_at', today())->count(),
            'deleteActions' => (clone $stats)->where(function ($query) {
                $query->where('action', 'like', '%delete%')->orWhere('action', 'like', '%destroy%');
            })->count(),
            'uniqueIps' => (clone $stats)->whereNotNull('ip_address')->distinct('ip_address')->count('ip_address'),
            'browsers' => (clone $stats)->whereNotNull('browser')->distinct()->orderBy('browser')->pluck('browser'),
            'systems' => (clone $stats)->whereNotNull('os')->distinct()->orderBy('os')->pluck('os'),
        ]);
    }

    public function destroy(Request $request, ActivityLog $auditLog)
    {
        abort_unless((int) $auditLog->user_id === (int) $request->user()->id, 404);

        $auditLog->delete();

        return redirect()
            ->route('advertiser.audit-logs')
            ->with('success', 'Audit log entry deleted successfully.');
    }
}
