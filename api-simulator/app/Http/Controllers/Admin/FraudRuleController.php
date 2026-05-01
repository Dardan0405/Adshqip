<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AntiFraudRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FraudRuleController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $rules = AntiFraudRule::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('rule_name', 'like', '%' . $search . '%')
                        ->orWhere('rule_type', 'like', '%' . $search . '%')
                        ->orWhere('match_value', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('rule_type'), fn ($query) => $query->where('rule_type', $request->input('rule_type')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderByDesc('is_active')
            ->orderBy('rule_name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => AntiFraudRule::count(),
            'active' => AntiFraudRule::where('is_active', true)->count(),
            'inactive' => AntiFraudRule::where('is_active', false)->count(),
            'triggers' => AntiFraudRule::sum('triggered_count'),
        ];

        return view('admin.fraud-rules.index', [
            'rules' => $rules,
            'stats' => $stats,
            'ruleTypes' => $this->ruleTypes(),
            'actions' => $this->actions(),
            'severities' => $this->severities(),
        ]);
    }

    public function store(Request $request)
    {
        AntiFraudRule::create($this->validatedRule($request));

        return redirect()->route('admin.fraud-rules')->with('success', 'Fraud rule created successfully.');
    }

    public function update(Request $request, AntiFraudRule $fraudRule)
    {
        $fraudRule->update($this->validatedRule($request));

        return redirect()->route('admin.fraud-rules')->with('success', 'Fraud rule updated successfully.');
    }

    public function activate(AntiFraudRule $fraudRule)
    {
        $fraudRule->update(['is_active' => true]);

        return redirect()->route('admin.fraud-rules')->with('success', 'Fraud rule activated successfully.');
    }

    public function deactivate(AntiFraudRule $fraudRule)
    {
        $fraudRule->update(['is_active' => false]);

        return redirect()->route('admin.fraud-rules')->with('success', 'Fraud rule deactivated successfully.');
    }

    public function destroy(AntiFraudRule $fraudRule)
    {
        $fraudRule->delete();

        return redirect()->route('admin.fraud-rules')->with('success', 'Fraud rule deleted successfully.');
    }

    private function validatedRule(Request $request): array
    {
        return $request->validate([
            'rule_name' => ['required', 'string', 'max:100'],
            'rule_type' => ['required', Rule::in(array_keys($this->ruleTypes()))],
            'match_value' => ['nullable', 'string', 'max:5000'],
            'threshold_value' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'reset_period_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'action' => ['required', Rule::in(array_keys($this->actions()))],
            'severity' => ['required', Rule::in(array_keys($this->severities()))],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }

    private function ruleTypes(): array
    {
        return [
            'click_cap' => 'Click Cap',
            'impression_cap' => 'Impression Cap',
            'ip_blacklist' => 'IP Blacklist',
            'ua_blacklist' => 'User Agent Blacklist',
            'geo_block' => 'Geo Block',
            'fingerprint' => 'Fingerprint',
        ];
    }

    private function actions(): array
    {
        return [
            'block' => 'Block',
            'flag' => 'Flag',
            'throttle' => 'Throttle',
        ];
    }

    private function severities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];
    }
}
