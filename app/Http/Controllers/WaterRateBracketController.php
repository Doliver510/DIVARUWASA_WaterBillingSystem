<?php

namespace App\Http\Controllers;

use App\Models\WaterRateBracket;
use Illuminate\Http\Request;

class WaterRateBracketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $brackets = WaterRateBracket::orderBy('sort_order')->get();

        return view('settings.rate-brackets', compact('brackets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'min_cubic' => 'required|integer|min:0',
            'max_cubic' => 'nullable|integer|min:1',
            'rate_per_cubic' => 'required|numeric|min:0',
        ]);

        // Auto-assign sort order
        $maxOrder = WaterRateBracket::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxOrder + 1;

        WaterRateBracket::create($validated);

        return redirect()->route('rate-brackets.index')->with('success', 'Rate bracket added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WaterRateBracket $rate_bracket)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'min_cubic' => 'required|integer|min:0',
            'max_cubic' => 'nullable|integer|min:1',
            'rate_per_cubic' => 'required|numeric|min:0',
        ]);

        $rate_bracket->update($validated);

        return redirect()->route('rate-brackets.index')->with('success', 'Rate bracket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WaterRateBracket $rate_bracket)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $rate_bracket->delete();

        return redirect()->route('rate-brackets.index')->with('success', 'Rate bracket deleted successfully.');
    }
}
