<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CyberService;
use App\Models\Domain;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CyberController extends Controller
{
    /**
     * Rôles autorisés à modifier le catalogue et les prix des prestations.
     *
     * @var list<string>
     */
    private const MANAGER_ROLES = ['administrateur', 'responsable', 'responsable_commercial'];

    public function index(Request $request): View
    {
        $services = CyberService::orderBy('sort_order')->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'code']);
        $canManage = $request->user()->hasRole(self::MANAGER_ROLES);

        $todayInvoices = Invoice::where('reference', 'like', 'FAC-CYB-%')
            ->whereDate('date', today())
            ->when(! $canManage, fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest('id')
            ->get(['id', 'reference', 'total', 'paid_amount', 'status', 'created_at']);

        return view('commercial.cyber.index', compact('services', 'customers', 'canManage', 'todayInvoices'));
    }

    /**
     * Enregistre les prestations réalisées et génère automatiquement la facture.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:especes,mobile_money,carte,credit'],
            'mobile_money_provider' => ['nullable', 'string', 'in:wave,orange_money'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.cyber_service_id' => ['nullable', 'exists:cyber_services,id'],
            'items.*.description' => ['required_without:items.*.cyber_service_id', 'nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required_without:items.*.cyber_service_id', 'nullable', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Ajoutez au moins une prestation.',
            'items.*.description.required_without' => 'Décrivez la prestation saisie librement.',
            'items.*.unit_price.required_without' => 'Indiquez le prix de la prestation saisie librement.',
        ]);

        $catalog = CyberService::whereIn('id', collect($validated['items'])->pluck('cyber_service_id')->filter())->get()->keyBy('id');

        $lines = collect($validated['items'])->map(function (array $item) use ($catalog) {
            $service = isset($item['cyber_service_id']) ? $catalog->get($item['cyber_service_id']) : null;
            $quantity = (int) $item['quantity'];
            // Le prix du catalogue fait foi : seules les prestations libres ont un prix saisi
            $unitPrice = $service ? (float) $service->price : (float) $item['unit_price'];

            return [
                'product_id' => null,
                'description' => $service ? $service->name : trim($item['description']),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount' => 0,
                'total' => $quantity * $unitPrice,
            ];
        });

        $total = $lines->sum('total');
        if ($total <= 0) {
            return back()->withInput()->with('error', 'Le montant total doit être supérieur à zéro.');
        }

        $invoice = DB::transaction(function () use ($validated, $lines, $total, $request) {
            $customer = isset($validated['customer_id'])
                ? Customer::find($validated['customer_id'])
                : Customer::firstOrCreate(
                    ['code' => 'CLI-PASSAGER'],
                    [
                        'name' => 'Client Comptoir / Passager',
                        'type' => 'particulier',
                        'status' => 'actif',
                        'user_id' => $request->user()->id,
                    ]
                );

            $domainId = Domain::where('code', 'PRINT')->value('id') ?? $customer->domain_id ?? Domain::value('id');
            $amountPaid = $validated['payment_method'] === 'credit' ? 0 : min($total, (float) $validated['amount_paid']);
            $notes = trim('Prestations cyber. '.($validated['notes'] ?? ''));

            $order = Order::create([
                'reference' => 'CYB-'.strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'domain_id' => $domainId,
                'user_id' => $request->user()->id,
                'date' => now(),
                'status' => 'livree',
                'subtotal' => $total,
                'tax_amount' => 0,
                'discount' => 0,
                'total' => $total,
                'notes' => $notes,
            ]);
            $order->items()->createMany($lines->all());

            $invoice = Invoice::create([
                'reference' => 'FAC-CYB-'.strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'domain_id' => $domainId,
                'user_id' => $request->user()->id,
                'date' => now(),
                'due_date' => now(),
                'type' => 'standard',
                'status' => $amountPaid >= $total ? 'payee' : ($amountPaid > 0 ? 'partielle' : 'non_payee'),
                'subtotal' => $total,
                'tax_amount' => 0,
                'discount' => 0,
                'total' => $total,
                'paid_amount' => $amountPaid,
                'notes' => $notes,
            ]);
            $invoice->items()->createMany($lines->all());

            if ($amountPaid > 0) {
                $paymentNotes = 'Paiement cyber';
                if ($validated['payment_method'] === 'mobile_money' && ! empty($validated['mobile_money_provider'])) {
                    $paymentNotes .= ' ('.($validated['mobile_money_provider'] === 'wave' ? 'Wave' : 'Orange Money').')';
                }

                Payment::create([
                    'reference' => 'PAY-CYB-'.strtoupper(Str::random(8)),
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'domain_id' => $domainId,
                    'user_id' => $request->user()->id,
                    'date' => now(),
                    'amount' => $amountPaid,
                    'method' => $validated['payment_method'],
                    'status' => 'valide',
                    'notes' => $paymentNotes,
                ]);
            }

            if ($customer->code !== 'CLI-PASSAGER' && $amountPaid > 0) {
                $customer->addLoyaltyPoints($amountPaid);
            }

            ActivityLogger::log('cyber_sale', 'Prestations cyber '.$invoice->reference.' d\'un montant de '.number_format($total, 0, ',', ' ').' FCFA', $invoice);

            return $invoice;
        });

        return redirect()->route('commercial.invoices.print', $invoice)->with('success', 'Prestations enregistrées. Facture générée automatiquement.');
    }

    public function storeService(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole(self::MANAGER_ROLES), 403);

        CyberService::create($this->validateService($request) + [
            'sort_order' => (int) CyberService::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Prestation ajoutée au catalogue du cyber.');
    }

    public function updateService(Request $request, CyberService $cyberService): RedirectResponse
    {
        abort_unless($request->user()->hasRole(self::MANAGER_ROLES), 403);

        $cyberService->update($this->validateService($request));

        return back()->with('success', 'Prestation « '.$cyberService->name.' » mise à jour.');
    }

    public function destroyService(Request $request, CyberService $cyberService): RedirectResponse
    {
        abort_unless($request->user()->hasRole(self::MANAGER_ROLES), 403);

        $cyberService->delete();

        return back()->with('success', 'Prestation supprimée du catalogue.');
    }

    /**
     * @return array{name: string, category: string, unit: string, price: float|int|string, is_active: bool}
     */
    private function validateService(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(CyberService::CATEGORIES))],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        return $validated + ['is_active' => $request->boolean('is_active')];
    }
}
