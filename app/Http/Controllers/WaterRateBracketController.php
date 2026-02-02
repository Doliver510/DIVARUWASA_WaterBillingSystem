<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BillingPeriodRate;
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
        $minCovers = (int) AppSetting::getValue('base_charge_covers_cubic', 10);
        
        // Check if current period is locked
        $currentPeriod = BillingPeriodRate::getCurrentPeriod();
        $currentPeriodLocked = BillingPeriodRate::isLocked($currentPeriod);

        return view('settings.rate-brackets', compact('brackets', 'minCovers', 'currentPeriod', 'currentPeriodLocked'));
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
            'min_cubic' => 'required|integer|min:1',
            'max_cubic' => 'nullable|integer|min:1',
            'rate_per_cubic' => 'required|numeric|min:0.01',
        ]);

        // Validate min < max
        if ($validated['max_cubic'] !== null && $validated['min_cubic'] >= $validated['max_cubic']) {
            return back()->withErrors([
                'max_cubic' => 'Maximum must be greater than minimum.',
            ])->withInput();
        }

        // Validate min_cubic is greater than minimum covers
        $minCovers = (int) AppSetting::getValue('base_charge_covers_cubic', 10);
        if ($validated['min_cubic'] <= $minCovers) {
            return back()->withErrors([
                'min_cubic' => "Bracket must start after minimum covers ({$minCovers} cu.m). Set minimum to at least " . ($minCovers + 1) . ".",
            ])->withInput();
        }

        // Check for overlapping brackets
        $overlap = $this->checkOverlap($validated['min_cubic'], $validated['max_cubic']);
        if ($overlap) {
            return back()->withErrors([
                'min_cubic' => "This range overlaps with existing bracket: {$overlap->min_cubic}-" . ($overlap->max_cubic ?? '∞') . " cu.m.",
            ])->withInput();
        }

        // Auto-assign sort order based on min_cubic
        $validated['sort_order'] = $validated['min_cubic'];

        WaterRateBracket::create($validated);

        return redirect()->route('rate-brackets.index')->with('success', 'Rate bracket added. Changes apply to future billing periods.');
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
            'min_cubic' => 'required|integer|min:1',
            'max_cubic' => 'nullable|integer|min:1',
            'rate_per_cubic' => 'required|numeric|min:0.01',
        ]);

        // Validate min < max
        if ($validated['max_cubic'] !== null && $validated['min_cubic'] >= $validated['max_cubic']) {
            return back()->withErrors([
                'max_cubic' => 'Maximum must be greater than minimum.',
            ])->withInput();
        }

        // Validate min_cubic is greater than minimum covers
        $minCovers = (int) AppSetting::getValue('base_charge_covers_cubic', 10);
        if ($validated['min_cubic'] <= $minCovers) {
            return back()->withErrors([
                'min_cubic' => "Bracket must start after minimum covers ({$minCovers} cu.m). Set minimum to at least " . ($minCovers + 1) . ".",
            ])->withInput();
        }

        // Check for overlapping brackets (excluding current bracket)
        $overlap = $this->checkOverlap($validated['min_cubic'], $validated['max_cubic'], $rate_bracket->id);
        if ($overlap) {
            return back()->withErrors([
                'min_cubic' => "This range overlaps with existing bracket: {$overlap->min_cubic}-" . ($overlap->max_cubic ?? '∞') . " cu.m.",
            ])->withInput();
        }

        // Update sort order based on min_cubic
        $validated['sort_order'] = $validated['min_cubic'];

        $rate_bracket->update($validated);

        return redirect()->route('rate-brackets.index')->with('success', 'Rate bracket updated. Changes apply to future billing periods.');
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

        return redirect()->route('rate-brackets.index')->with('success', 'Rate bracket deleted. Changes apply to future billing periods.');
    }

    /**
     * Check if a range overlaps with existing brackets.
     */
    private function checkOverlap(int $min, ?int $max, ?int $excludeId = null): ?WaterRateBracket
    {
        $query = WaterRateBracket::query();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $brackets = $query->get();

        foreach ($brackets as $bracket) {
            $bracketMax = $bracket->max_cubic ?? PHP_INT_MAX;
            $newMax = $max ?? PHP_INT_MAX;

            // Check if ranges overlap: (min1 <= max2) && (max1 >= min2)
            if ($min <= $bracketMax && $newMax >= $bracket->min_cubic) {
                return $bracket;
            }
        }

        return null;
    }
}
