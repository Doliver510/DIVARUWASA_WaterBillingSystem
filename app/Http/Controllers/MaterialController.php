<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($validated) {
            $material = Material::create($validated);

            // Log initial stock if any
            if ($validated['stock_quantity'] > 0) {
                StockMovement::create([
                    'material_id' => $material->id,
                    'user_id' => Auth::id(),
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $validated['stock_quantity'],
                    'stock_before' => 0,
                    'stock_after' => $validated['stock_quantity'],
                    'reference_type' => 'initial_stock',
                    'reference_id' => null,
                    'remarks' => 'Initial stock when material was created',
                ]);
            }
        });

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

    /**
     * Display stock movement history.
     */
    public function stockMovements(Request $request): View
    {
        $query = StockMovement::with(['material', 'user'])
            ->orderByDesc('created_at');

        // Filter by material
        if ($request->filled('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(25);
        $materials = Material::orderBy('name')->get();

        // Summary stats
        $totalIn = StockMovement::where('type', 'in')->sum('quantity');
        $totalOut = StockMovement::where('type', 'out')->sum('quantity');

        return view('materials.stock-movements', [
            'movements' => $movements,
            'materials' => $materials,
            'totalIn' => $totalIn,
            'totalOut' => abs($totalOut),
        ]);
    }
}
