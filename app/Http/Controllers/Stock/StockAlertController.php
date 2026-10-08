<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\StockAlert;
use App\Services\ActivityLogger;

class StockAlertController extends Controller
{
    public function index()
    {
        $alerts = StockAlert::with(['product', 'warehouse'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock.alerts.index', compact('alerts'));
    }

    public function resolve(StockAlert $alert)
    {
        $alert->update(['status' => 'resolved']);

        ActivityLogger::log('resolve_stock_alert', "Résolution de l'alerte de stock pour le produit {$alert->product->name}", $alert);

        return redirect()->route('stock.alerts.index')->with('success', 'Alerte marquée comme résolue.');
    }
}
