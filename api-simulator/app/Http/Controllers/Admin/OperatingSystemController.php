<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperatingSystem;
use Illuminate\Http\Request;

class OperatingSystemController extends Controller
{
    private const DEVICE_OPTIONS = [
        'desktop' => [
            'windows_pc' => 'Windows PC',
            'macbook' => 'MacBook',
            'imac' => 'iMac',
            'linux_desktop' => 'Linux Desktop',
            'chromebook' => 'Chromebook',
        ],
        'mobile' => [
            'iphone' => 'iPhone',
            'samsung_galaxy' => 'Samsung Galaxy',
            'google_pixel' => 'Google Pixel',
            'huawei' => 'Huawei',
            'xiaomi' => 'Xiaomi',
            'oneplus' => 'OnePlus',
            'oppo' => 'Oppo',
            'vivo' => 'Vivo',
            'realme' => 'Realme',
            'nokia' => 'Nokia',
        ],
        'tablet' => [
            'ipad' => 'iPad',
            'ipad_pro' => 'iPad Pro',
            'samsung_tab' => 'Samsung Tab',
            'amazon_fire' => 'Amazon Fire',
            'lenovo_tab' => 'Lenovo Tab',
            'microsoft_surface' => 'Microsoft Surface',
        ],
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $operatingSystems = OperatingSystem::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('os_name', 'like', '%' . $search . '%')
                        ->orWhere('os_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('os_name')
            ->orderBy('os_value')
            ->paginate(20)
            ->withQueryString();

        return view('admin.operating-systems.index', [
            'operatingSystems' => $operatingSystems,
            'deviceOptions' => self::DEVICE_OPTIONS,
            'deviceLabels' => $this->getDeviceLabels(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'os_name' => ['required', 'string', 'max:100'],
            'os_value' => ['required', 'string', 'max:100'],
            'devices' => ['required', 'array', 'min:1'],
            'devices.*' => ['string', 'in:' . implode(',', array_keys($this->getDeviceLabels()))],
        ]);

        OperatingSystem::create($validated);

        return redirect()
            ->route('admin.operating-systems')
            ->with('success', 'Operating System created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $operatingSystem = OperatingSystem::findOrFail($id);

        $validated = $request->validate([
            'os_name' => ['required', 'string', 'max:100'],
            'os_value' => ['required', 'string', 'max:100'],
            'devices' => ['required', 'array', 'min:1'],
            'devices.*' => ['string', 'in:' . implode(',', array_keys($this->getDeviceLabels()))],
        ]);

        $operatingSystem->update($validated);

        return redirect()
            ->route('admin.operating-systems')
            ->with('success', 'Operating System updated successfully.');
    }

    public function block(int $id)
    {
        $operatingSystem = OperatingSystem::findOrFail($id);
        $operatingSystem->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.operating-systems')
            ->with('success', 'Operating System blocked successfully.');
    }

    public function unblock(int $id)
    {
        $operatingSystem = OperatingSystem::findOrFail($id);
        $operatingSystem->update(['status' => 'active']);

        return redirect()
            ->route('admin.operating-systems')
            ->with('success', 'Operating System unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $operatingSystem = OperatingSystem::findOrFail($id);
        $operatingSystem->delete();

        return redirect()
            ->route('admin.operating-systems')
            ->with('success', 'Operating System deleted successfully.');
    }

    private function getDeviceLabels(): array
    {
        return array_reduce(
            self::DEVICE_OPTIONS,
            fn (array $carry, array $group) => $carry + $group,
            []
        );
    }
}
