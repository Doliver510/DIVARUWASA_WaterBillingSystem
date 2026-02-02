<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BillingPeriodRate;
use App\Models\WaterRateBracket;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $settings = AppSetting::all();
        
        // Check if current period is locked
        $currentPeriod = BillingPeriodRate::getCurrentPeriod();
        $currentPeriodLocked = BillingPeriodRate::isLocked($currentPeriod);

        return view('settings.index', compact('settings', 'currentPeriod', 'currentPeriodLocked'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required',
        ]);

        // Validate minimum covers against water rate brackets
        if (isset($request->settings['base_charge_covers_cubic'])) {
            $minCovers = (int) $request->settings['base_charge_covers_cubic'];
            $firstBracket = WaterRateBracket::orderBy('min_cubic')->first();

            if ($firstBracket && $minCovers >= $firstBracket->min_cubic) {
                return back()->withErrors([
                    'base_charge_covers_cubic' => "Minimum covers ({$minCovers} cu.m) must be less than the first rate bracket start ({$firstBracket->min_cubic} cu.m). Adjust rate brackets first.",
                ])->withInput();
            }
        }

        foreach ($request->settings as $key => $value) {
            AppSetting::where('key', $key)->update(['value' => $value]);
        }

        // Note: Changes apply to future periods. Current locked periods keep their snapshotted rates.
        return redirect()->route('settings.index')->with('success', 'Settings updated successfully. Changes will apply to future billing periods.');
    }
}
