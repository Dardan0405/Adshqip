<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataExportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataExportJobController extends Controller
{
    private const DATASETS = [
        'users' => ['label' => 'Users', 'table' => 'aq_users', 'date' => 'created_at'],
        'advertisers' => ['label' => 'Advertisers', 'table' => 'aq_users', 'date' => 'created_at', 'role' => 'advertiser'],
        'publishers' => ['label' => 'Publishers', 'table' => 'aq_users', 'date' => 'created_at', 'role' => 'publisher'],
        'campaigns' => ['label' => 'Campaigns', 'table' => 'aq_campaigns', 'date' => 'created_at'],
        'pricing_plans' => ['label' => 'Pricing Plans', 'table' => 'aq_pricing_plans', 'date' => 'created_at'],
        'case_studies' => ['label' => 'Case Studies', 'table' => 'aq_case_studies', 'date' => 'created_at'],
        'platform_announcements' => ['label' => 'Platform Announcements', 'table' => 'aq_platform_announcements', 'date' => 'created_at'],
        'ad_serving_logs' => ['label' => 'Ad Serving Logs', 'table' => 'aq_ad_serving_logs', 'date' => 'created_at'],
        'fraud_events' => ['label' => 'Fraud Events', 'table' => 'aq_fraud_events', 'date' => 'created_at'],
        'notifications' => ['label' => 'Notification Log', 'table' => 'aq_notifications', 'date' => 'created_at'],
        'transactions' => ['label' => 'Transactions', 'table' => 'aq_transactions', 'date' => 'created_at'],
    ];

    public function index(Request $request)
    {
        $jobs = DataExportJob::query()
            ->with('createdBy:id,email,role')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('dataset', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('dataset'), fn ($query) => $query->where('dataset', $request->input('dataset')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => DataExportJob::count(),
            'completed' => DataExportJob::where('status', 'completed')->count(),
            'failed' => DataExportJob::where('status', 'failed')->count(),
            'rows' => DataExportJob::where('status', 'completed')->sum('row_count'),
        ];

        return view('admin.data-export-jobs.index', [
            'jobs' => $jobs,
            'summary' => $summary,
            'datasets' => $this->availableDatasets(),
            'statuses' => ['pending', 'running', 'completed', 'failed'],
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatedPayload($request);
        $dataset = self::DATASETS[$payload['dataset']];

        $job = DataExportJob::create([
            'created_by' => Auth::id(),
            'name' => $payload['name'] ?: $dataset['label'] . ' Export',
            'dataset' => $payload['dataset'],
            'format' => 'csv',
            'status' => 'pending',
            'filters' => [
                'date_from' => $payload['date_from'] ?? null,
                'date_to' => $payload['date_to'] ?? null,
                'limit' => $payload['limit'],
            ],
            'expires_at' => now()->addDays(7),
        ]);

        $this->runExport($job);

        return redirect()->route('admin.data-export-jobs')->with('success', 'Data export job created.');
    }

    public function retry(DataExportJob $dataExportJob)
    {
        $dataExportJob->update([
            'status' => 'pending',
            'row_count' => 0,
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
            'failed_at' => null,
            'expires_at' => now()->addDays(7),
        ]);

        $this->runExport($dataExportJob);

        return redirect()->route('admin.data-export-jobs')->with('success', 'Data export job retried.');
    }

    public function download(DataExportJob $dataExportJob)
    {
        abort_unless($dataExportJob->is_downloadable, 404);
        abort_unless(Storage::disk('local')->exists($dataExportJob->file_path), 404);

        return Storage::disk('local')->download($dataExportJob->file_path, $dataExportJob->file_name);
    }

    public function destroy(DataExportJob $dataExportJob)
    {
        if ($dataExportJob->file_path && Storage::disk('local')->exists($dataExportJob->file_path)) {
            Storage::disk('local')->delete($dataExportJob->file_path);
        }

        $dataExportJob->delete();

        return redirect()->route('admin.data-export-jobs')->with('success', 'Data export job deleted.');
    }

    public function clearExpired()
    {
        $jobs = DataExportJob::whereNotNull('expires_at')->where('expires_at', '<', now())->get();

        foreach ($jobs as $job) {
            if ($job->file_path && Storage::disk('local')->exists($job->file_path)) {
                Storage::disk('local')->delete($job->file_path);
            }

            $job->delete();
        }

        return redirect()->route('admin.data-export-jobs')->with('success', 'Expired export jobs cleared.');
    }

    private function runExport(DataExportJob $job): void
    {
        $job->update(['status' => 'running', 'started_at' => now()]);

        try {
            $definition = self::DATASETS[$job->dataset] ?? null;

            if (! $definition || ! Schema::hasTable($definition['table'])) {
                throw new \RuntimeException('Dataset table is not available.');
            }

            $rows = $this->queryRows($job, $definition);
            $csv = $this->toCsv($rows);
            $fileName = Str::slug($job->name) . '-' . now()->format('Ymd-His') . '.csv';
            $filePath = 'exports/data-export-jobs/' . $fileName;

            Storage::disk('local')->put($filePath, $csv);

            $job->update([
                'status' => 'completed',
                'row_count' => count($rows),
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => Storage::disk('local')->size($filePath),
                'completed_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);
        } catch (\Throwable $exception) {
            $job->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => Str::limit($exception->getMessage(), 2000, ''),
            ]);
        }
    }

    private function queryRows(DataExportJob $job, array $definition): array
    {
        $filters = $job->filters ?? [];
        $query = DB::table($definition['table']);

        if (($definition['role'] ?? null) && Schema::hasColumn($definition['table'], 'role')) {
            $query->where('role', $definition['role']);
        }

        if (($filters['date_from'] ?? null) && Schema::hasColumn($definition['table'], $definition['date'])) {
            $query->whereDate($definition['date'], '>=', $filters['date_from']);
        }

        if (($filters['date_to'] ?? null) && Schema::hasColumn($definition['table'], $definition['date'])) {
            $query->whereDate($definition['date'], '<=', $filters['date_to']);
        }

        if (Schema::hasColumn($definition['table'], 'id')) {
            $query->orderByDesc('id');
        }

        return $query
            ->limit((int) ($filters['limit'] ?? 1000))
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function toCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($rows === []) {
            fputcsv($stream, ['No rows matched this export.']);
        } else {
            $headers = array_keys($rows[0]);
            fputcsv($stream, $headers);

            foreach ($rows as $row) {
                fputcsv($stream, array_map(fn ($value) => $this->stringify($value), $row));
            }
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv ?: '';
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value) ?: '';
        }

        return (string) $value;
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'name' => ['nullable', 'string', 'max:160'],
            'dataset' => ['required', 'in:' . implode(',', array_keys($this->availableDatasets()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'limit' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);
    }

    private function availableDatasets(): array
    {
        return collect(self::DATASETS)
            ->filter(fn ($definition) => Schema::hasTable($definition['table']))
            ->all();
    }
}
