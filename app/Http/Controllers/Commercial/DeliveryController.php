<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Delivery::query()->with(['order', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->latest()->paginate(12)->withQueryString();
        $orders = Order::whereDoesntHave('delivery')->latest()->take(20)->get();
        $customers = Customer::where('is_active', true)->get();

        $counts = [
            'total' => Delivery::count(),
            'preparation' => Delivery::where('status', 'en_preparation')->count(),
            'in_transit' => Delivery::whereIn('status', ['expediee', 'en_cours'])->count(),
            'delivered' => Delivery::where('status', 'livree')->count(),
        ];

        return view('commercial.deliveries.index', compact('deliveries', 'orders', 'customers', 'counts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'exists:orders,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:50'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $tracking = 'LIV-'.date('Y').'-'.str_pad((string) (Delivery::count() + 1), 4, '0', STR_PAD_LEFT);

        Delivery::create([
            'tracking_number' => $tracking,
            'order_id' => $validated['order_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'driver_name' => $validated['driver_name'] ?? null,
            'driver_phone' => $validated['driver_phone'] ?? null,
            'delivery_address' => $validated['delivery_address'],
            'city' => $validated['city'] ?? 'Abidjan',
            'status' => 'en_preparation',
            'scheduled_at' => $validated['scheduled_at'] ?? Carbon::now()->addDay(),
        ]);

        return redirect()->route('deliveries.index')->with('success', "Expédition {$tracking} programmée.");
    }

    public function updateStatus(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:en_preparation,expediee,en_cours,livree,echec'],
            'recipient_name' => ['nullable', 'string'],
        ]);

        $delivery->status = $validated['status'];
        if ($validated['status'] === 'livree') {
            $delivery->delivered_at = Carbon::now();
            $delivery->recipient_name = $validated['recipient_name'] ?? 'Destinataire';
            if ($delivery->order) {
                $delivery->order->update(['status' => 'livrée']);
                StockService::deductOrderStock($delivery->order);
            }
        }
        $delivery->save();

        return back()->with('success', "Statut de livraison mis à jour : {$delivery->status}.");
    }
}
