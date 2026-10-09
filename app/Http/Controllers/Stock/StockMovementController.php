<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse', 'product', 'user', 'destinationWarehouse']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20);
        $warehouses = Warehouse::active()->get();
        // Since products can be many, maybe we shouldn't load them all for filter but let's assume so for now
        $products = Product::where('is_active', true)->get();

        return view('stock.movements.index', compact('movements', 'warehouses', 'products'));
    }

    public function create()
    {
        $warehouses = Warehouse::active()->get();
        $products = Product::where('is_active', true)->get();

        // Pass current stock levels for vue/alpine to update dynamically if needed
        return view('stock.movements.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:entree,sortie,transfert,retour,ajustement',
            'reason_motif' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => ['required', 'integer', $request->input('type') === 'ajustement' ? 'min:0' : 'min:1'],
            'destination_warehouse_id' => 'required_if:type,transfert|nullable|exists:warehouses,id|different:warehouse_id',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        try {
            StockService::processMovement($validated);

            return redirect()->route('stock.movements.index')->with('success', 'Mouvement de stock enregistré avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
