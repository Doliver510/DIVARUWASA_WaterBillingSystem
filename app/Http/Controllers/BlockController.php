<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\User;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    /**
     * Display blocks management page.
     */
    public function index()
    {
        $blocks = Block::ordered()->withCount('consumers')->get();

        return view('settings.blocks', compact('blocks'));
    }

    /**
     * Store a new block.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:blocks,name',
        ]);

        Block::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('settings.blocks')
            ->with('success', 'Block added successfully.');
    }

    /**
     * Delete a block (only if no consumers).
     */
    public function destroy(Block $block)
    {
        if ($block->consumers()->count() > 0) {
            return redirect()->route('settings.blocks')
                ->with('error', 'Cannot delete block with existing consumers.');
        }

        $block->delete();

        return redirect()->route('settings.blocks')
            ->with('success', 'Block deleted successfully.');
    }

    /**
     * Display block assignments page.
     */
    public function assignments()
    {
        $blocks = Block::ordered()->with('meterReaders')->get();
        $meterReaders = User::whereHas('role', fn ($q) => $q->where('slug', 'meter-reader'))
            ->with('assignedBlocks')
            ->get();

        return view('settings.block-assignments', compact('blocks', 'meterReaders'));
    }

    /**
     * Update block assignments for a meter reader.
     */
    public function updateAssignments(Request $request, User $user)
    {
        $validated = $request->validate([
            'block_ids' => 'nullable|array',
            'block_ids.*' => 'exists:blocks,id',
        ]);

        $user->assignedBlocks()->sync($validated['block_ids'] ?? []);

        return redirect()->route('settings.block-assignments')
            ->with('success', "Block assignments updated for {$user->full_name}.");
    }
}
