<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function destroy(Request $request, Warehouse $warehouse)
    {
        if (Warehouse::count() <= 1) {
            return back()->with('error', "Impossible de supprimer le seul entrepôt de l'entreprise. Vous devez conserver au moins un site de stockage actif.");
        }

        $warehouseName = $warehouse->name;
        $remainingStocks = $warehouse->warehouseStocks()->where('physical_quantity', '>', 0)->get();
        $totalUnits = (int) $remainingStocks->sum('physical_quantity');

        // Si l'utilisateur a choisi de transférer les stocks vers un autre entrepôt avant suppression
        if ($request->filled('transfer_to_warehouse_id') && $totalUnits > 0) {
            $destWarehouse = Warehouse::findOrFail($request->input('transfer_to_warehouse_id'));

            if ($destWarehouse->id === $warehouse->id) {
                return back()->with('error', "L'entrepôt de destination doit être différent de l'entrepôt à supprimer.");
            }

            DB::transaction(function () use ($warehouse, $destWarehouse, $remainingStocks) {
                foreach ($remainingStocks as $ws) {
                    StockService::processMovement([
                        'warehouse_id' => $warehouse->id,
                        'destination_warehouse_id' => $destWarehouse->id,
                        'product_id' => $ws->product_id,
                        'domain_id' => $ws->product?->domain_id,
                        'user_id' => auth()->id(),
                        'type' => 'transfert',
                        'reason_motif' => 'Transfert avant suppression entrepôt',
                        'quantity' => $ws->physical_quantity,
                        'reference' => 'TRF-CLOTURE-'.strtoupper(Str::random(5)),
                        'notes' => "Transfert automatique de clôture vers {$destWarehouse->name} avant suppression de {$warehouse->name}",
                        'date' => now(),
                    ]);
                }

                $warehouse->delete();
            });

            ActivityLogger::log('delete_warehouse', "Suppression de l'entrepôt {$warehouseName} (stocks transférés vers {$destWarehouse->name})", null);

            return redirect()->route('stock.warehouses.index')->with('success', "L'entrepôt '{$warehouseName}' a été supprimé et ses {$totalUnits} unité(s) de stock ont été transférées vers '{$destWarehouse->name}'.");
        }

        // Si des stocks physiques existent et que la suppression forcée n'a pas été confirmée
        if ($totalUnits > 0 && ! $request->boolean('force_delete')) {
            return back()->with('error', "Cet entrepôt contient encore {$totalUnits} unité(s) en stock. Veuillez transférer les stocks vers un autre entrepôt ou confirmer la suppression forcée.");
        }

        $warehouse->delete();
        ActivityLogger::log('delete_warehouse', "Suppression de l'entrepôt: {$warehouseName}", null);

        return redirect()->route('stock.warehouses.index')->with('success', "L'entrepôt '{$warehouseName}' a été supprimé avec succès.");
    }
}
