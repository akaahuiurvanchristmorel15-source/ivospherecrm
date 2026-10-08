<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Revenue;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DomainController extends Controller
{
    /**
     * Display a listing of the domains.
     */
    public function index(Request $request): View
    {
        $query = Domain::withCount('users');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $domains = $query->orderBy('name')->get();
        $totalCount = Domain::count();
        $activeCount = Domain::where('is_active', true)->count();
        $totalUsers = DB::table('domain_user')->distinct('user_id')->count('user_id');

        return view('admin.domains.index', compact('domains', 'totalCount', 'activeCount', 'totalUsers'));
    }

    /**
     * Show the form for creating one or multiple domains.
     */
    public function create(): View
    {
        return view('admin.domains.create');
    }

    /**
     * Show the form for editing the specified domain.
     */
    public function edit(Domain $domain): View
    {
        $domain->loadCount('users');

        return view('admin.domains.edit', compact('domain'));
    }

    /**
     * Store a newly created domain (single or delegated batch).
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->has('domains') && is_array($request->input('domains'))) {
            return $this->storeBatch($request);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:domains,code'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Le nom du domaine est obligatoire.',
            'code.required' => 'Le code du domaine est obligatoire.',
            'code.unique' => 'Ce code de domaine existe déjà.',
            'code.max' => 'Le code ne peut pas dépasser 50 caractères.',
        ]);

        $isActive = $request->boolean('is_active', true);
        $code = strtoupper(trim($validated['code']));

        $domain = Domain::create([
            'name' => trim($validated['name']),
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? 'indigo',
            'icon' => $validated['icon'] ?? 'briefcase',
            'status' => $isActive ? 'actif' : 'inactif',
            'is_active' => $isActive,
        ]);

        ActivityLogger::log(
            action: 'creation_domaine',
            description: "Création du domaine {$domain->name} ({$domain->code})",
            domainId: $domain->id,
            subject: $domain
        );

        return $this->redirectAfterAction($request, "Le domaine {$domain->name} a été créé avec succès.");
    }

    /**
     * Store multiple domains in a single batch transaction.
     */
    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domains' => ['required', 'array', 'min:1'],
            'domains.*.name' => ['required', 'string', 'max:255'],
            'domains.*.code' => ['required', 'string', 'max:50', 'distinct', 'unique:domains,code'],
            'domains.*.description' => ['nullable', 'string'],
            'domains.*.color' => ['nullable', 'string', 'max:50'],
            'domains.*.icon' => ['nullable', 'string', 'max:50'],
            'domains.*.is_active' => ['nullable', 'boolean'],
        ], [
            'domains.required' => 'Veuillez ajouter au moins un domaine.',
            'domains.min' => 'Veuillez ajouter au moins un domaine.',
            'domains.*.name.required' => 'Le nom de chaque domaine est obligatoire.',
            'domains.*.code.required' => 'Le code de chaque domaine est obligatoire.',
            'domains.*.code.unique' => 'Le code :input est déjà utilisé par un autre domaine.',
            'domains.*.code.distinct' => 'Le code :input a été saisi plusieurs fois dans le formulaire.',
        ]);

        $createdCount = 0;

        DB::transaction(function () use ($validated, &$createdCount) {
            foreach ($validated['domains'] as $item) {
                if (empty(trim($item['name'] ?? '')) && empty(trim($item['code'] ?? ''))) {
                    continue;
                }

                $isActive = isset($item['is_active']) ? (bool) $item['is_active'] : true;
                $code = strtoupper(trim($item['code']));
                $name = trim($item['name']);

                $domain = Domain::create([
                    'name' => $name,
                    'code' => $code,
                    'description' => $item['description'] ?? null,
                    'color' => ! empty($item['color']) ? $item['color'] : 'indigo',
                    'icon' => ! empty($item['icon']) ? $item['icon'] : 'briefcase',
                    'status' => $isActive ? 'actif' : 'inactif',
                    'is_active' => $isActive,
                ]);

                ActivityLogger::log(
                    action: 'creation_domaine_groupee',
                    description: "Création groupée : Domaine {$name} ({$code})",
                    domainId: $domain->id,
                    subject: $domain
                );

                $createdCount++;
            }
        });

        return $this->redirectAfterAction($request, "{$createdCount} domaine(s) ajouté(s) avec succès !");
    }

    /**
     * Update the specified domain.
     */
    public function update(Request $request, Domain $domain): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:domains,code,'.$domain->id],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'in:actif,inactif'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Le nom du domaine est obligatoire.',
            'code.required' => 'Le code du domaine est obligatoire.',
            'code.unique' => 'Ce code est déjà utilisé par un autre domaine.',
            'color.required' => 'La couleur est obligatoire.',
        ]);

        $isActive = $request->has('is_active')
            ? (bool) $request->input('is_active')
            : ($request->input('status') === 'actif');

        $domain->update([
            'name' => trim($validated['name']),
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'],
            'icon' => $validated['icon'] ?? $domain->icon,
            'status' => $isActive ? 'actif' : 'inactif',
            'is_active' => $isActive,
        ]);

        ActivityLogger::log(
            action: 'modification_domaine',
            description: "A modifié les paramètres du domaine {$domain->name} ({$domain->code})",
            domainId: $domain->id,
            subject: $domain
        );

        return $this->redirectAfterAction($request, "Le domaine {$domain->name} a été mis à jour avec succès.");
    }

    /**
     * Toggle domain active state.
     */
    public function toggleStatus(Domain $domain): RedirectResponse
    {
        $domain->is_active = ! $domain->is_active;
        $domain->status = $domain->is_active ? 'actif' : 'inactif';
        $domain->save();

        ActivityLogger::log(
            action: 'changement_statut_domaine',
            description: 'A '.($domain->is_active ? 'activé' : 'désactivé')." le domaine {$domain->name}",
            domainId: $domain->id,
            subject: $domain
        );

        return back()->with('success', "Statut du domaine {$domain->name} actualisé.");
    }

    /**
     * Remove the specified domain from storage.
     */
    public function destroy(Request $request, Domain $domain): RedirectResponse
    {
        $name = $domain->name;

        DB::transaction(function () use ($domain) {
            // Detach associated users from domain_user pivot
            $domain->users()->detach();

            // Nullify domain_id on directly related tables
            Product::where('domain_id', $domain->id)->update(['domain_id' => null]);
            Order::where('domain_id', $domain->id)->update(['domain_id' => null]);
            Invoice::where('domain_id', $domain->id)->update(['domain_id' => null]);
            Employee::where('domain_id', $domain->id)->update(['domain_id' => null]);
            CashRegister::where('domain_id', $domain->id)->update(['domain_id' => null]);
            Expense::where('domain_id', $domain->id)->update(['domain_id' => null]);
            Revenue::where('domain_id', $domain->id)->update(['domain_id' => null]);

            $domain->delete();
        });

        ActivityLogger::log(
            action: 'suppression_domaine',
            description: "Suppression du domaine {$name}"
        );

        return $this->redirectAfterAction($request, "Le domaine {$name} a été supprimé avec succès.");
    }

    /**
     * Helper to redirect back to referer if appropriate or fallback to domains index.
     */
    protected function redirectAfterAction(Request $request, string $message): RedirectResponse
    {
        $referer = $request->header('referer');
        if ($referer && ! str_contains($referer, '/create') && ! str_contains($referer, '/edit')) {
            return redirect()->to($referer)->with('success', $message);
        }

        return redirect()->route('admin.domains.index')->with('success', $message);
    }
}
