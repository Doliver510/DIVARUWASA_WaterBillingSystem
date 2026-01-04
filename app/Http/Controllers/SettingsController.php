<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        if (auth()->user()->role->slug !== 'admin') {
            abort(403);
        }

        $settings = AppSetting::all();

        return view('settings.index', compact('settings'));
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

        foreach ($request->settings as $key => $value) {
            AppSetting::where('key', $key)->update(['value' => $value]);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
