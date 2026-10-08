<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with('domain');

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        $warehouses = $query->paginate(20);

        // Append stock summary
        foreach ($warehouses as $warehouse) {
            $latestIds = StockMovement::where('warehouse_id', $warehouse->id)
                ->select(DB::raw('MAX(id) as id'))
                ->groupBy('product_id')
                ->pluck('id');

            $warehouse->product_count = StockMovement::whereIn('id', $latestIds)->count();
            $warehouse->total_items = StockMovement::whereIn('id', $latestIds)->sum('stock_after');
        }

        $domains = Domain::all();

        return view('stock.warehouses.index', compact('warehouses', 'domains'));
    }

    public function create()
    {
        $domains = Domain::all();

        return view('stock.warehouses.create_edit', compact('domains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'domain_id' => 'nullable|exists:domains,id',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $warehouse = Warehouse::create($validated);

        ActivityLogger::log('create_warehouse', "Création de l'entrepôt: {$warehouse->name}", $warehouse);

        return redirect()->route('stock.warehouses.index')->with('success', 'Entrepôt créé avec succès.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('domain');

        $latestIds = StockMovement::where('warehouse_id', $warehouse->id)
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('product_id')
            ->pluck('id');

        $stockLevels = StockMovement::with('product')
            ->whereIn('id', $latestIds)
            ->get();

        $recentMovements = StockMovement::with(['product', 'user', 'destinationWarehouse'])
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('stock.warehouses.show', compact('warehouse', 'stockLevels', 'recentMovements'));
    }

    public function edit(Warehouse $warehouse)
    {
        $domains = Domain::all();

        return view('stock.warehouses.create_edit', compact('warehouse', 'domains'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,'.$warehouse->id,
            'domain_id' => 'nullable|exists:domains,id',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'manager' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $warehouse->update($validated);

        ActivityLogger::log('update_warehouse', "Mise à jour de l'entrepôt: {$warehouse->name}", $warehouse);

        return redirect()->route('stock.warehouses.index')->with('success', 'Entrepôt mis à jour avec succès.');
    }

    public function destroy(Warehouse $warehouse)
    {
        // Add soft deletes or logic, for now simple delete
        ActivityLogger::log('delete_warehouse', "Suppression de l'entrepôt: {$warehouse->name}", null);
        $warehouse->delete();

        return redirect()->route('stock.warehouses.index')->with('success', 'Entrepôt supprimé avec succès.');
    }
}
