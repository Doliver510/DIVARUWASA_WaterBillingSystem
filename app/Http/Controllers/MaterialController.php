<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials.
     */
    public function index(): View
    {
        $materials = Material::orderBy('name')->get();

        return view('materials.index', compact('materials'));
    }

    /**
     * Store a newly created material.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        Material::create($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Material added successfully.');
    }

    /**
     * Update the specified material.
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')
            ->with('success', 'Material updated successfully.');
    }

    /**
     * Remove the specified material.
     */
    public function destroy(Material $material): RedirectResponse
    {
        // Check if material is used in any maintenance request
        if ($material->maintenanceMaterials()->exists()) {
            return redirect()->route('materials.index')
                ->with('error', 'Cannot delete material that has been used in maintenance requests.');
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material deleted successfully.');
    }

    /**
     * Add stock to material.
     */
    public function addStock(Request $request, Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $material->addStock($validated['quantity'], $validated['notes'] ?? null);

        return redirect()->route('materials.index')
            ->with('success', "Added {$validated['quantity']} {$material->unit} to {$material->name} stock.");
    }
}
