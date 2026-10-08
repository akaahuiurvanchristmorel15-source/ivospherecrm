<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\FinancialAccount;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    /**
     * Display a listing of cash registers with filters and domain options.
     */
    public function index(Request $request): View
    {
        $query = CashRegister::with('domain');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('domain_id')) {
            if ($request->domain_id === 'central') {
                $query->whereNull('domain_id');
            } else {
                $query->where('domain_id', $request->domain_id);
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $cashRegisters = $query->orderBy('name')->get();
        $totalBalance = (float) CashRegister::sum('balance');
        $activeCount = CashRegister::where('is_active', true)->count();
        $totalCount = CashRegister::count();
        $domains = Domain::all();

        return view('finance.cash_registers.index', compact(
            'cashRegisters',
            'totalBalance',
            'activeCount',
            'totalCount',
            'domains'
        ));
    }

    /**
     * Show the form for creating one or multiple cash registers.
     */
    public function create(): View
    {
        $domains = Domain::all();

        return view('finance.cash_registers.create', compact('domains'));
    }

    /**
     * Store a newly created cash register (single or batch).
     */
    public function store(Request $request): RedirectResponse
    {
        // If batch payload provided via single route, forward to storeBatch
        if ($request->has('registers') && is_array($request->input('registers'))) {
            return $this->storeBatch($request);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:cash_registers,code',
            'domain_id' => 'nullable|exists:domains,id',
            'balance' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom de la caisse est obligatoire.',
            'code.required' => 'Le code de la caisse est obligatoire.',
            'code.max' => 'Le code ne peut pas dépasser 30 caractères.',
            'code.unique' => 'Ce code de caisse est déjà utilisé.',
            'balance.min' => 'Le solde initial ne peut pas être négatif.',
        ]);

        $balance = isset($validated['balance']) && is_numeric($validated['balance']) ? (float) $validated['balance'] : 0.0;
        $isActive = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        $cashRegister = DB::transaction(function () use ($validated, $balance, $isActive) {
            $reg = CashRegister::create([
                'name' => trim($validated['name']),
                'code' => strtoupper(trim($validated['code'])),
                'domain_id' => ! empty($validated['domain_id']) ? (int) $validated['domain_id'] : null,
                'balance' => $balance,
                'is_active' => $isActive,
            ]);

            // Sync with FinancialAccount for treasury consistency
            $domainName = $reg->domain?->name;
            FinancialAccount::firstOrCreate(
                ['code' => $reg->code],
                [
                    'name' => $reg->name,
                    'type' => 'caisse',
                    'domain_id' => $reg->domain_id,
                    'institution_name' => $domainName ? 'Pôle '.$domainName : 'Siège / Trésorerie Centrale',
                    'account_number' => $reg->code,
                    'balance' => $reg->balance,
                    'initial_balance' => $reg->balance,
                    'currency' => 'FCFA',
                    'is_active' => $reg->is_active,
                    'notes' => 'Créée via la gestion des caisses',
                ]
            );

            return $reg;
        });

        ActivityLogger::log('creation_caisse', 'Création d\'une caisse '.$cashRegister->name.' ('.$cashRegister->code.')', $cashRegister);

        return redirect()->route('finance.cash-registers.index')->with('success', 'Caisse créée avec succès.');
    }

    /**
     * Store multiple cash registers in a single batch transaction.
     */
    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registers' => 'required|array|min:1',
            'registers.*.name' => 'required|string|max:255',
            'registers.*.code' => 'required|string|max:30|distinct|unique:cash_registers,code',
            'registers.*.domain_id' => 'nullable|exists:domains,id',
            'registers.*.balance' => 'nullable|numeric|min:0',
            'registers.*.is_active' => 'nullable|boolean',
        ], [
            'registers.required' => 'Veuillez renseigner au moins une caisse.',
            'registers.min' => 'Veuillez renseigner au moins une caisse.',
            'registers.*.name.required' => 'Le nom de chaque caisse est obligatoire.',
            'registers.*.code.required' => 'Le code de chaque caisse est obligatoire.',
            'registers.*.code.max' => 'Les codes de caisse ne doivent pas dépasser 30 caractères.',
            'registers.*.code.unique' => 'Le code :input est déjà utilisé par une autre caisse.',
            'registers.*.code.distinct' => 'Le code :input a été saisi plusieurs fois dans le formulaire.',
            'registers.*.balance.min' => 'Le solde initial ne peut pas être négatif.',
        ]);

        $createdCount = 0;

        DB::transaction(function () use ($validated, &$createdCount) {
            foreach ($validated['registers'] as $item) {
                if (empty(trim($item['name'] ?? '')) && empty(trim($item['code'] ?? ''))) {
                    continue;
                }

                $balance = isset($item['balance']) && is_numeric($item['balance']) ? (float) $item['balance'] : 0.0;
                $isActive = isset($item['is_active']) ? (bool) $item['is_active'] : true;
                $code = strtoupper(trim($item['code']));
                $name = trim($item['name']);
                $domainId = ! empty($item['domain_id']) ? (int) $item['domain_id'] : null;

                $cashRegister = CashRegister::create([
                    'name' => $name,
                    'code' => $code,
                    'domain_id' => $domainId,
                    'balance' => $balance,
                    'is_active' => $isActive,
                ]);

                // Sync with FinancialAccount
                $domainName = $cashRegister->domain?->name;
                FinancialAccount::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'type' => 'caisse',
                        'domain_id' => $domainId,
                        'institution_name' => $domainName ? 'Pôle '.$domainName : 'Siège / Trésorerie Centrale',
                        'account_number' => $code,
                        'balance' => $balance,
                        'initial_balance' => $balance,
                        'currency' => 'FCFA',
                        'is_active' => $isActive,
                        'notes' => 'Créée via l\'ajout groupé de caisses',
                    ]
                );

                ActivityLogger::log('creation_caisse_groupee', "Création groupée : Caisse {$name} ({$code})", $cashRegister);
                $createdCount++;
            }
        });

        return redirect()->route('finance.cash-registers.index')
            ->with('success', "{$createdCount} caisse(s) ajoutée(s) avec succès !");
    }

    /**
     * Display the specified cash register with its transactions.
     */
    public function show(CashRegister $cashRegister): View
    {
        $cashRegister->load('domain');
        $transactions = $cashRegister->transactions()->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15);

        return view('finance.cash_registers.show', compact('cashRegister', 'transactions'));
    }

    /**
     * Update the specified cash register.
     */
    public function update(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        $oldCode = $cashRegister->code;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:cash_registers,code,'.$cashRegister->id,
            'domain_id' => 'nullable|exists:domains,id',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Le nom de la caisse est obligatoire.',
            'code.required' => 'Le code de la caisse est obligatoire.',
            'code.max' => 'Le code ne peut pas dépasser 30 caractères.',
            'code.unique' => 'Ce code est déjà utilisé.',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['domain_id'] = ! empty($validated['domain_id']) ? (int) $validated['domain_id'] : null;
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : $cashRegister->is_active;

        $cashRegister->update($validated);

        // Keep FinancialAccount synchronized
        FinancialAccount::where('code', $oldCode)->update([
            'name' => $cashRegister->name,
            'code' => $cashRegister->code,
            'domain_id' => $cashRegister->domain_id,
            'is_active' => $cashRegister->is_active,
        ]);

        ActivityLogger::log('modification_caisse', 'Modification d\'une caisse', $cashRegister);

        return redirect()->route('finance.cash-registers.index')->with('success', 'Caisse mise à jour avec succès.');
    }

    /**
     * Toggle the active status of a cash register.
     */
    public function toggleStatus(CashRegister $cashRegister): RedirectResponse
    {
        $cashRegister->update([
            'is_active' => ! $cashRegister->is_active,
        ]);

        FinancialAccount::where('code', $cashRegister->code)->update([
            'is_active' => $cashRegister->is_active,
        ]);

        $statusLabel = $cashRegister->is_active ? 'activée' : 'désactivée';
        ActivityLogger::log('statut_caisse', "Caisse {$cashRegister->name} {$statusLabel}", $cashRegister);

        return redirect()->route('finance.cash-registers.index')
            ->with('success', "La caisse {$cashRegister->name} a été {$statusLabel} avec succès.");
    }

    /**
     * Remove the specified cash register from storage if no records attached.
     */
    public function destroy(CashRegister $cashRegister): RedirectResponse
    {
        $hasTransactions = $cashRegister->transactions()->exists();
        $hasExpenses = $cashRegister->expenses()->exists();
        $hasRevenues = $cashRegister->revenues()->exists();

        if ($hasTransactions || $hasExpenses || $hasRevenues) {
            return redirect()->route('finance.cash-registers.index')
                ->with('error', 'Impossible de supprimer cette caisse car des écritures ou transactions financières y sont attachées. Vous pouvez la désactiver à la place.');
        }

        $matchingAccount = FinancialAccount::where('code', $cashRegister->code)->first();
        if ($matchingAccount) {
            $hasAccountOperations = $matchingAccount->transactions()->exists()
                || $matchingAccount->transfersIn()->exists()
                || $matchingAccount->transfersOut()->exists()
                || $matchingAccount->cashRegisterSessions()->exists();

            if (! $hasAccountOperations) {
                $matchingAccount->delete();
            }
        }

        $name = $cashRegister->name;
        $cashRegister->delete();

        ActivityLogger::log('suppression_caisse', "Suppression de la caisse {$name}");

        return redirect()->route('finance.cash-registers.index')
            ->with('success', "La caisse {$name} a été supprimée avec succès.");
    }
}
