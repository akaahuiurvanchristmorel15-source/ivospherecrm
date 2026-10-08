<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quotation;
use App\Models\SupportTicket;
use App\Models\TechProject;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function index(Request $request): View
    {
        $customerId = $request->get('customer_id');

        $customer = $customerId
            ? Customer::find($customerId)
            : Customer::orderByDesc('balance')->first();

        if (! $customer) {
            $customer = Customer::create([
                'first_name' => 'Jean',
                'last_name' => 'Kouassi',
                'company_name' => 'Entreprise Ivoirienne SARL',
                'email' => 'client@ivosphere.com',
                'phone' => '+225 07 00 00 00 01',
                'balance' => 450000,
                'is_active' => true,
            ]);
        }

        $allCustomers = Customer::where('is_active', true)->take(20)->get();

        $quotations = Quotation::where('customer_id', $customer->id)->latest()->take(5)->get();
        $orders = Order::where('customer_id', $customer->id)->latest()->take(5)->get();
        $invoices = Invoice::where('customer_id', $customer->id)->latest()->take(5)->get();
        $projects = TechProject::where('customer_id', $customer->id)->latest()->take(3)->get();
        $tickets = SupportTicket::where('customer_id', $customer->id)->latest()->take(5)->get();
        $deliveries = Delivery::where('customer_id', $customer->id)->latest()->take(5)->get();

        $totalInvoiced = (float) Invoice::where('customer_id', $customer->id)->sum('total');
        $totalPaid = (float) Invoice::where('customer_id', $customer->id)->sum('paid_amount');
        $balanceDue = (float) Invoice::where('customer_id', $customer->id)->sum('balance');

        return view('client_portal.index', compact(
            'customer',
            'allCustomers',
            'quotations',
            'orders',
            'invoices',
            'projects',
            'tickets',
            'deliveries',
            'totalInvoiced',
            'totalPaid',
            'balanceDue'
        ));
    }
}
