<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user')->latest('created_at');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('action', 'like', '%' . $search . '%')
                    ->orWhere('ip_address', 'like', '%' . $search . '%')
                    ->orWhere('browser', 'like', '%' . $search . '%')
                    ->orWhere('os', 'like', '%' . $search . '%')
                    ->orWhere('user_agent', 'like', '%' . $search . '%');
            });
        }

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        if ($browser = $request->get('browser')) {
            $query->where('browser', $browser);
        }

        if ($os = $request->get('os')) {
            $query->where('os', $os);
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'totalLogs' => ActivityLog::count(),
            'todayLogs' => ActivityLog::whereDate('created_at', today())->count(),
            'deleteActions' => ActivityLog::where('action', 'like', '%delete%')
                ->orWhere('action', 'like', '%destroy%')
                ->count(),
            'uniqueIps' => ActivityLog::whereNotNull('ip_address')->distinct('ip_address')->count('ip_address'),
            'browsers' => ActivityLog::whereNotNull('browser')->distinct()->orderBy('browser')->pluck('browser'),
            'systems' => ActivityLog::whereNotNull('os')->distinct()->orderBy('os')->pluck('os'),
        ]);
    }

    public function destroy(ActivityLog $auditLog)
    {
        $auditLog->delete();

        return redirect()
            ->route('admin.audit-logs')
            ->with('success', 'Audit log entry deleted successfully.');
    }
}
