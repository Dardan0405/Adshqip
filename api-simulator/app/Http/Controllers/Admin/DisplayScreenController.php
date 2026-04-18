<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisplayScreen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisplayScreenController extends Controller
{
    public function index()
    {
        $screens = DisplayScreen::query()
            ->orderBy('screen_name')
            ->paginate(20);

        return view('admin.display-screens.index', [
            'screens' => $screens,
            'stats' => [
                'total' => DisplayScreen::count(),
                'active' => DisplayScreen::where('status', 'active')->count(),
                'blocked' => DisplayScreen::where('status', 'blocked')->count(),
                'largest' => DisplayScreen::query()
                    ->orderByRaw('(width * height) desc')
                    ->first(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'screen_name' => ['required', 'string', 'max:120'],
            'value' => ['required', 'string', 'max:120', 'alpha_dash', 'unique:aq_display_screens,value'],
            'width' => ['required', 'integer', 'min:1', 'max:100000'],
            'height' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DisplayScreen::create([
            'screen_name' => trim($validated['screen_name']),
            'value' => strtolower(trim($validated['value'])),
            'width' => $validated['width'],
            'height' => $validated['height'],
            'status' => 'active',
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.display-screens')
            ->with('success', 'Display screen created successfully.');
    }

    public function update(Request $request, DisplayScreen $displayScreen)
    {
        $validated = $request->validate([
            'screen_name' => ['required', 'string', 'max:120'],
            'value' => [
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('aq_display_screens', 'value')->ignore($displayScreen->id),
            ],
            'width' => ['required', 'integer', 'min:1', 'max:100000'],
            'height' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $displayScreen->update([
            'screen_name' => trim($validated['screen_name']),
            'value' => strtolower(trim($validated['value'])),
            'width' => $validated['width'],
            'height' => $validated['height'],
        ]);

        return redirect()
            ->route('admin.display-screens')
            ->with('success', 'Display screen updated successfully.');
    }

    public function block(DisplayScreen $displayScreen)
    {
        $displayScreen->update(['status' => 'blocked']);

        return redirect()
            ->route('admin.display-screens')
            ->with('success', 'Display screen blocked successfully.');
    }

    public function unblock(DisplayScreen $displayScreen)
    {
        $displayScreen->update(['status' => 'active']);

        return redirect()
            ->route('admin.display-screens')
            ->with('success', 'Display screen unblocked successfully.');
    }
}
