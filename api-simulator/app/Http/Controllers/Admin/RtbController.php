<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RtbController extends Controller
{
    public function index(Request $request)
    {
        $query = AdExchange::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('endpoint_url', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $exchanges = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalExchanges = AdExchange::count();
        $dspCount = AdExchange::where('type', 'DSP')->count();
        $sspCount = AdExchange::where('type', 'SSP')->count();
        $activeCount = AdExchange::where('status', 'active')->count();
        $testingCount = AdExchange::where('status', 'testing')->count();

        return view('admin.rtb.index', compact(
            'exchanges',
            'totalExchanges',
            'dspCount',
            'sspCount',
            'activeCount',
            'testingCount'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'type' => 'required|in:DSP,SSP',
            'endpoint_url' => 'required|url|max:500',
            'auth_type' => 'nullable|in:api_key,oauth2,basic,none',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'authentication_key' => 'nullable|string|max:500',
            'auction_currency' => 'required|string|size:3',
            'auction_type' => 'required|in:1,2',
            'is_strict_openrtb' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,testing',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Prepare credentials JSON
        $credentials = [];
        if ($request->filled('username')) {
            $credentials['username'] = $request->username;
        }
        if ($request->filled('password')) {
            $credentials['password'] = $request->password; // In production, encrypt this
        }
        if ($request->filled('authentication_key')) {
            $credentials['authentication_key'] = $request->authentication_key;
        }

        AdExchange::create([
            'name' => $request->name,
            'type' => $request->type,
            'endpoint_url' => $request->endpoint_url,
            'auth_type' => $request->auth_type ?? 'api_key',
            'credentials' => !empty($credentials) ? $credentials : null,
            'auction_currency' => $request->auction_currency,
            'auction_type' => $request->auction_type,
            'is_strict_openrtb' => $request->boolean('is_strict_openrtb', true),
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.rtb')->with('success', 'RTB Ad Exchange created successfully.');
    }

    public function show($id)
    {
        $exchange = AdExchange::findOrFail($id);

        // Extract credentials from JSON
        $credentials = $exchange->credentials ?? [];

        return response()->json([
            'id' => $exchange->id,
            'name' => $exchange->name,
            'type' => $exchange->type,
            'endpoint_url' => $exchange->endpoint_url,
            'auth_type' => $exchange->auth_type,
            'username' => $credentials['username'] ?? '',
            'password' => $credentials['password'] ?? '',
            'authentication_key' => $credentials['authentication_key'] ?? '',
            'auction_currency' => $exchange->auction_currency,
            'auction_type' => $exchange->auction_type,
            'is_strict_openrtb' => $exchange->is_strict_openrtb,
            'status' => $exchange->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $exchange = AdExchange::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'type' => 'required|in:DSP,SSP',
            'endpoint_url' => 'required|url|max:500',
            'auth_type' => 'nullable|in:api_key,oauth2,basic,none',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'authentication_key' => 'nullable|string|max:500',
            'auction_currency' => 'required|string|size:3',
            'auction_type' => 'required|in:1,2',
            'is_strict_openrtb' => 'nullable|boolean',
            'status' => 'nullable|in:active,inactive,testing',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Prepare credentials JSON
        $credentials = [];
        if ($request->filled('username')) {
            $credentials['username'] = $request->username;
        }
        if ($request->filled('password')) {
            $credentials['password'] = $request->password; // In production, encrypt this
        }
        if ($request->filled('authentication_key')) {
            $credentials['authentication_key'] = $request->authentication_key;
        }

        $exchange->update([
            'name' => $request->name,
            'type' => $request->type,
            'endpoint_url' => $request->endpoint_url,
            'auth_type' => $request->auth_type ?? 'api_key',
            'credentials' => !empty($credentials) ? $credentials : null,
            'auction_currency' => $request->auction_currency,
            'auction_type' => $request->auction_type,
            'is_strict_openrtb' => $request->boolean('is_strict_openrtb', true),
            'status' => $request->status ?? 'active',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'RTB Ad Exchange updated successfully.']);
        }

        return redirect()->route('admin.rtb')->with('success', 'RTB Ad Exchange updated successfully.');
    }

    public function block($id)
    {
        $exchange = AdExchange::findOrFail($id);
        $exchange->update(['status' => 'inactive']);

        return response()->json(['success' => true, 'message' => 'Ad Exchange blocked successfully.']);
    }

    public function unblock($id)
    {
        $exchange = AdExchange::findOrFail($id);
        $exchange->update(['status' => 'active']);

        return response()->json(['success' => true, 'message' => 'Ad Exchange unblocked successfully.']);
    }

    public function destroy($id)
    {
        $exchange = AdExchange::findOrFail($id);
        $exchange->delete();

        return response()->json(['success' => true, 'message' => 'Ad Exchange deleted successfully.']);
    }

    public function export(Request $request)
    {
        $query = AdExchange::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('endpoint_url', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $exchanges = $query->orderBy('created_at', 'desc')->get();

        $filename = 'rtb_exchanges_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($exchanges) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Platform', 'Ad-Exchange Name', 'Ping URL', 'Auction Currency', 'Auction Type', 'Status', 'Created At']);

            foreach ($exchanges as $exchange) {
                $auctionType = $exchange->auction_type == 1 ? 'First Bid' : 'Second Bid';

                fputcsv($file, [
                    $exchange->id,
                    $exchange->type,
                    $exchange->name,
                    $exchange->endpoint_url,
                    $exchange->auction_currency,
                    $auctionType,
                    ucfirst($exchange->status),
                    $exchange->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
