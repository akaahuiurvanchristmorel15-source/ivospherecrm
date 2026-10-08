<?php

namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Domain;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contract::query()->with(['domain', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->boolean('expiring_soon')) {
            $thirtyDaysAhead = Carbon::now()->addDays(30)->toDateString();
            $query->where('status', 'actif')
                ->whereNotNull('end_date')
                ->where('end_date', '<=', $thirtyDaysAhead)
                ->where('end_date', '>=', Carbon::now()->toDateString());
        }

        $contracts = $query->orderBy('end_date', 'asc')->paginate(12)->withQueryString();
        $domains = Domain::all();

        $stats = [
            'total' => Contract::count(),
            'active' => Contract::where('status', 'actif')->count(),
            'expiring' => Contract::where('status', 'actif')
                ->whereNotNull('end_date')
                ->where('end_date', '<=', Carbon::now()->addDays(30)->toDateString())
                ->where('end_date', '>=', Carbon::now()->toDateString())
                ->count(),
            'total_amount' => (float) Contract::where('status', 'actif')->sum('amount'),
        ];

        return view('contracts.index', compact('contracts', 'domains', 'stats'));
    }

    public function create(): View
    {
        $domains = Domain::all();
        $nextRef = 'CTR-'.date('Y').'-'.str_pad((string) (Contract::count() + 1), 4, '0', STR_PAD_LEFT);

        return view('contracts.create_edit', [
            'contract' => new Contract(['reference' => $nextRef, 'currency' => 'FCFA', 'status' => 'actif']),
            'domains' => $domains,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', 'unique:contracts,reference'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'party_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'string'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['alert_days'] = [90, 60, 30, 7];

        $contract = Contract::create($validated);

        return redirect()->route('contracts.show', $contract)->with('success', "Contrat {$contract->reference} enregistré avec succès.");
    }

    public function show(Contract $contract): View
    {
        $contract->load(['domain', 'user']);

        return view('contracts.show', compact('contract'));
    }

    public function edit(Contract $contract): View
    {
        $domains = Domain::all();

        return view('contracts.create_edit', [
            'contract' => $contract,
            'domains' => $domains,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Contract $contract): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', 'unique:contracts,reference,'.$contract->id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'party_name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'string'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $contract->update($validated);

        return redirect()->route('contracts.show', $contract)->with('success', 'Contrat mis à jour avec succès.');
    }

    public function destroy(Contract $contract): RedirectResponse
    {
        $contract->delete();

        return redirect()->route('contracts.index')->with('success', 'Contrat supprimé.');
    }
}
