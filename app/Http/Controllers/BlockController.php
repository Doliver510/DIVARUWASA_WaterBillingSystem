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
        $meterReaders = User::whereHas('role', fn ($q) => $q->where('slug', 'meter-reader'))->get();

        return view('settings.blocks', compact('blocks', 'meterReaders'));
    }

    /**
     * Store a new block.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'block_number' => 'required|integer|min:0|unique:blocks,block_number',
        ]);

        Block::create([
            'block_number' => $validated['block_number'],
            'name' => Block::generateName($validated['block_number']),
            'is_active' => true,
        ]);

        return redirect()->route('settings.blocks')
            ->with('success', 'Block added successfully.');
    }

    /**
     * Toggle block active status.
     */
    public function toggleStatus(Block $block)
    {
        $block->update(['is_active' => ! $block->is_active]);

        $status = $block->is_active ? 'activated' : 'deactivated';

        return redirect()->route('settings.blocks')
            ->with('success', "Block {$block->name} {$status}.");
    }

    /**
     * Delete a block (only if no consumers).
     */
    public function destroy(Block $block)
    {
        if ($block->consumers()->count() > 0) {
            return redirect()->route('settings.blocks')
                ->with('error', 'Cannot delete block with existing consumers. Deactivate it instead.');
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
        $blocks = Block::active()->ordered()->with('meterReaders')->get();
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
