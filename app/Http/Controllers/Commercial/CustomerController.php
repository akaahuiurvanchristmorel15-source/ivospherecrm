<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\PhoneNumberFormatter;
use App\Support\TenantRule;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(15);

        return view('commercial.customers.index', compact('customers'));
    }

    public function create()
    {
        $domains = Domain::active()->get();
        $commercials = User::active()->get();

        return view('commercial.customers.create_edit', compact('domains', 'commercials'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'commercial_id' => 'nullable|exists:users,id',
            'code' => ['required', 'string', TenantRule::unique('customers', 'code')],
            'type' => 'required|in:particulier,entreprise',
            'category' => 'nullable|in:standard,vip,revendeur,institutionnel',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'phone2' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'whatsapp' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'loyalty_level' => 'nullable|in:BRONZE,SILVER,GOLD,PREMIUM',
            'status' => 'required|in:actif,inactif',
            'notes' => 'nullable|string',
        ], ['phone.phone' => PhoneNumberFormatter::invalidMessage(), 'phone2.phone' => PhoneNumberFormatter::invalidMessage(), 'whatsapp.phone' => PhoneNumberFormatter::invalidMessage()], ['phone' => 'de téléphone', 'phone2' => 'de téléphone secondaire', 'whatsapp' => 'WhatsApp']);

        $validated['user_id'] = auth()->id();
        $customer = Customer::create($validated);

        ActivityLogger::log('created_customer', 'Création du client '.$customer->name, $customer);

        return redirect()->route('commercial.customers.show', $customer)->with('success', 'Client créé avec succès.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'quotations.user',
            'orders.items.product',
            'invoices.payments',
            'payments',
            'appointments.commercial',
            'commercial',
            'domain',
        ]);

        return view('commercial.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $domains = Domain::active()->get();
        $commercials = User::active()->get();

        return view('commercial.customers.create_edit', compact('customer', 'domains', 'commercials'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'commercial_id' => 'nullable|exists:users,id',
            'code' => ['required', 'string', TenantRule::unique('customers', 'code', $customer->id)],
            'type' => 'required|in:particulier,entreprise',
            'category' => 'nullable|in:standard,vip,revendeur,institutionnel',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'phone2' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'whatsapp' => ['nullable', 'string', 'max:30', PhoneNumberFormatter::rule()],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'loyalty_level' => 'nullable|in:BRONZE,SILVER,GOLD,PREMIUM',
            'status' => 'required|in:actif,inactif',
            'notes' => 'nullable|string',
        ], ['phone.phone' => PhoneNumberFormatter::invalidMessage(), 'phone2.phone' => PhoneNumberFormatter::invalidMessage(), 'whatsapp.phone' => PhoneNumberFormatter::invalidMessage()], ['phone' => 'de téléphone', 'phone2' => 'de téléphone secondaire', 'whatsapp' => 'WhatsApp']);

        $customer->update($validated);

        ActivityLogger::log('updated_customer', 'Modification du client '.$customer->name, $customer);

        return redirect()->route('commercial.customers.show', $customer)->with('success', 'Client mis à jour avec succès.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        ActivityLogger::log('deleted_customer', 'Suppression du client '.$customer->name, $customer);

        return redirect()->route('commercial.customers.index')->with('success', 'Client supprimé avec succès.');
    }
}
