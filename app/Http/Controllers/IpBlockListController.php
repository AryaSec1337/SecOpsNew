<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIpBlockListRequest;
use App\Http\Requests\UpdateIpBlockListRequest;
use App\Models\IpBlockList;
use Illuminate\Http\Request;

class IpBlockListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default to current year and week
        $year = $request->query('year', now()->year);
        $week = $request->query('week', now()->weekOfYear);

        $query = IpBlockList::where('year', $year)->where('week_number', $week);

        // Optional status filter
        if ($request->has('status') && $request->status !== 'All') {
            $query->where('status', $request->status);
        }

        $list = $query->latest()->get();

        // Get available weeks/years for the dropdown filter
        $availablePeriods = IpBlockList::select('year', 'week_number')
            ->distinct()
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->get();

        // Stats
        $stats = [
            'total' => $list->count(),
            'pending' => $list->where('status', 'Pending')->count(),
            'blocked' => $list->where('status', 'Blocked')->count(),
        ];

        return view('ip-block-lists.index', compact('list', 'year', 'week', 'availablePeriods', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'source' => 'nullable|string',
            'description' => 'required|string',
        ]);

        $year = now()->year;
        $week = now()->weekOfYear;

        // Check if already sent to this week's list
        $exists = IpBlockList::where('ip_address', $request->ip_address)
            ->where('year', $year)
            ->where('week_number', $week)
            ->exists();

        if ($exists) {
            return back()->with('error', 'IP is already in the block list for this week!');
        }

        IpBlockList::create([
            'ip_address' => $request->ip_address,
            'source' => $request->source ?? 'Manual',
            'description' => $request->description,
            'week_number' => $week,
            'year' => $year,
            'status' => 'Pending'
        ]);

        return back()->with('success', 'IP successfully added to the Weekly Block List.');
    }

    public function update(Request $request, IpBlockList $ipBlockList)
    {
        $request->validate([
            'status' => 'required|in:Pending,Blocked,Ignored'
        ]);

        $ipBlockList->update(['status' => $request->status]);

        return back()->with('success', "Status updated to {$request->status}.");
    }

    public function destroy(IpBlockList $ipBlockList)
    {
        $ipBlockList->delete();
        return back()->with('success', 'IP removed from the block list.');
    }
}
