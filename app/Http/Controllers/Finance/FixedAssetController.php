<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Employee;
use App\Models\FixedAsset;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FixedAssetController extends Controller
{
    public function index(Request $request): View
    {
        $query = FixedAsset::with(['category', 'domain', 'responsibleEmployee', 'usages', 'rentals', 'maintenances']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('domain_id')) {
            $query->byDomain((int) $request->domain_id);
        }

        if ($request->filled('category_id')) {
            $query->where('asset_category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_rental_eligible')) {
            $query->where('is_rental_eligible', (bool) $request->is_rental_eligible);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // ── KPIs Globaux du Parc ───────────────────────────────────────────
        $allAssets = FixedAsset::with(['usages', 'rentals', 'maintenances'])->get();

        $totalAcquisitionValue = (float) $allAssets->sum('acquisition_value');
        $totalDepreciationCumul = (float) $allAssets->sum(fn (FixedAsset $a) => $a->accumulated_depreciation);
        $totalVnc = (float) $allAssets->sum(fn (FixedAsset $a) => $a->net_book_value);
        $totalUsageRevenue = (float) $allAssets->sum(fn (FixedAsset $a) => $a->total_usage_revenue);
        $totalRentalRevenue = (float) $allAssets->sum(fn (FixedAsset $a) => $a->total_rental_revenue);
        $totalRevenueGenerated = $totalUsageRevenue + $totalRentalRevenue;
        $totalMaintenanceCost = (float) $allAssets->sum(fn (FixedAsset $a) => $a->total_maintenance_costs);
        $netContribution = $totalRevenueGenerated - $totalMaintenanceCost - $totalDepreciationCumul;

        $countsByStatus = [
            'total' => $allAssets->count(),
            'en_service' => $allAssets->where('status', 'en_service')->count(),
            'disponible' => $allAssets->where('status', 'disponible')->count(),
            'loue' => $allAssets->where('status', 'loue')->count(),
            'en_maintenance' => $allAssets->where('status', 'en_maintenance')->count(),
            'hors_service' => $allAssets->whereIn('status', ['hors_service', 'a_reparer'])->count(),
        ];

        $categories = AssetCategory::orderBy('name')->get();
        $domains = Domain::orderBy('name')->get();

        return view('finance.assets.index', compact(
            'assets',
            'categories',
            'domains',
            'totalAcquisitionValue',
            'totalDepreciationCumul',
            'totalVnc',
            'totalUsageRevenue',
            'totalRentalRevenue',
            'totalRevenueGenerated',
            'totalMaintenanceCost',
            'netContribution',
            'countsByStatus'
        ));
    }

    public function create(): View
    {
        $categories = AssetCategory::orderBy('name')->get();
        $domains = Domain::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::orderBy('first_name')->get();

        return view('finance.assets.create_edit', compact('categories', 'domains', 'suppliers', 'warehouses', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'domain_id' => 'nullable|exists:domains,id',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'acquisition_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_price' => 'required|numeric|min:0',
            'additional_fees' => 'nullable|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:0|max:50',
            'depreciation_method' => 'required|string|in:lineaire,degressif,non_amortissable',
            'depreciation_start_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'responsible_employee_id' => 'nullable|exists:employees,id',
            'responsible_department' => 'nullable|string|max:100',
            'status' => 'required|string|in:en_service,disponible,loue,en_maintenance,a_reparer,hors_service,vendu,cede',
            'condition' => 'required|string|in:neuf,tres_bon,bon_etat,a_reparer,hors_service',
            'is_rental_eligible' => 'nullable|boolean',
            'rental_price_per_day' => 'nullable|numeric|min:0',
            'rental_deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
            'purchase_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:8192',
        ]);

        $validated['additional_fees'] = (float) ($validated['additional_fees'] ?? 0);
        $validated['residual_value'] = (float) ($validated['residual_value'] ?? 0);
        $validated['acquisition_value'] = (float) $validated['purchase_price'] + $validated['additional_fees'];
        $validated['is_rental_eligible'] = $request->boolean('is_rental_eligible');

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('assets/photos', 'public');
        }

        if ($request->hasFile('purchase_document')) {
            $validated['purchase_document_path'] = $request->file('purchase_document')->store('assets/documents', 'public');
        }

        $asset = FixedAsset::create($validated);

        ActivityLogger::log('creation_immobilisation', "Création de l'immobilisation {$asset->code} - {$asset->name}");

        return redirect()->route('finance.assets.show', $asset)->with('success', "L'immobilisation {$asset->code} a été enregistrée avec succès.");
    }

    public function show(FixedAsset $asset): View
    {
        $asset->load([
            'category',
            'domain',
            'supplier',
            'warehouse',
            'responsibleEmployee',
            'depreciations',
            'usages.customer',
            'usages.order',
            'usages.user',
            'rentals.customer',
            'rentals.user',
            'maintenances.supplier',
            'maintenances.user',
        ]);

        $customers = Customer::orderBy('company_name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('finance.assets.show', compact('asset', 'customers', 'suppliers'));
    }

    public function edit(FixedAsset $asset): View
    {
        $categories = AssetCategory::orderBy('name')->get();
        $domains = Domain::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $employees = Employee::orderBy('first_name')->get();

        return view('finance.assets.create_edit', compact('asset', 'categories', 'domains', 'suppliers', 'warehouses', 'employees'));
    }

    public function update(Request $request, FixedAsset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'domain_id' => 'nullable|exists:domains,id',
            'description' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'acquisition_date' => 'required|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_price' => 'required|numeric|min:0',
            'additional_fees' => 'nullable|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:0|max:50',
            'depreciation_method' => 'required|string|in:lineaire,degressif,non_amortissable',
            'depreciation_start_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'responsible_employee_id' => 'nullable|exists:employees,id',
            'responsible_department' => 'nullable|string|max:100',
            'status' => 'required|string|in:en_service,disponible,loue,en_maintenance,a_reparer,hors_service,vendu,cede',
            'condition' => 'required|string|in:neuf,tres_bon,bon_etat,a_reparer,hors_service',
            'is_rental_eligible' => 'nullable|boolean',
            'rental_price_per_day' => 'nullable|numeric|min:0',
            'rental_deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:4096',
            'purchase_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:8192',
        ]);

        $validated['additional_fees'] = (float) ($validated['additional_fees'] ?? 0);
        $validated['residual_value'] = (float) ($validated['residual_value'] ?? 0);
        $validated['acquisition_value'] = (float) $validated['purchase_price'] + $validated['additional_fees'];
        $validated['is_rental_eligible'] = $request->boolean('is_rental_eligible');

        if ($request->hasFile('photo')) {
            if ($asset->photo_path && Storage::disk('public')->exists($asset->photo_path)) {
                Storage::disk('public')->delete($asset->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('assets/photos', 'public');
        }

        if ($request->hasFile('purchase_document')) {
            if ($asset->purchase_document_path && Storage::disk('public')->exists($asset->purchase_document_path)) {
                Storage::disk('public')->delete($asset->purchase_document_path);
            }
            $validated['purchase_document_path'] = $request->file('purchase_document')->store('assets/documents', 'public');
        }

        $oldUsefulLife = $asset->useful_life_years;
        $oldAcquisitionValue = $asset->acquisition_value;

        $asset->update($validated);

        if ($oldUsefulLife !== $asset->useful_life_years || (float) $oldAcquisitionValue !== (float) $asset->acquisition_value) {
            $asset->generateDepreciationSchedule();
        }

        ActivityLogger::log('modification_immobilisation', "Modification de l'immobilisation {$asset->code}");

        return redirect()->route('finance.assets.show', $asset)->with('success', "L'immobilisation {$asset->code} a été mise à jour.");
    }

    public function destroy(FixedAsset $asset): RedirectResponse
    {
        if ($asset->usages()->exists() || $asset->rentals()->exists()) {
            return back()->with('error', "Impossible de supprimer l'actif {$asset->code} car des prestations ou locations lui sont déjà rattachées. Vous pouvez plutôt le déclarer comme 'Vendu' ou 'Hors service'.");
        }

        $code = $asset->code;
        $asset->delete();

        ActivityLogger::log('suppression_immobilisation', "Suppression de l'immobilisation {$code}");

        return redirect()->route('finance.assets.index')->with('success', "L'immobilisation {$code} a été supprimée.");
    }

    public function dispose(Request $request, FixedAsset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'disposal_type' => 'required|in:vendu,cede,mis_au_rebut',
            'disposal_date' => 'required|date',
            'disposal_price' => 'nullable|numeric|min:0',
            'disposal_reason' => 'required|string|max:500',
        ]);

        $asset->update([
            'status' => $validated['disposal_type'] === 'mis_au_rebut' ? 'hors_service' : $validated['disposal_type'],
            'disposed_at' => $validated['disposal_date'],
            'disposal_price' => $validated['disposal_price'] ?? 0,
            'disposal_reason' => $validated['disposal_reason'],
        ]);

        ActivityLogger::log('cession_immobilisation', "Sortie d'actif {$asset->code} ({$validated['disposal_type']})");

        return back()->with('success', "La sortie de l'actif {$asset->code} a été enregistrée.");
    }

    public function export(Request $request): Response
    {
        $assets = FixedAsset::with(['category', 'domain', 'usages', 'rentals', 'maintenances'])->get();

        $csv = "Code;Nom;Domaine;Catégorie;Date Acquisition;Prix Achat (FCFA);Frais Annexes (FCFA);Valeur Acquisition (FCFA);Amort. Cumulé (FCFA);VNC Actuelle (FCFA);CA Prestations (FCFA);CA Locations (FCFA);CA Total (FCFA);Coûts Maintenance (FCFA);Rentabilité Nette (FCFA);ROI (%)\n";

        foreach ($assets as $a) {
            $csv .= sprintf(
                '"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s";"%s%%"'."\n",
                $a->code,
                str_replace('"', '""', $a->name),
                $a->domain?->name ?? 'N/A',
                $a->category?->name ?? 'N/A',
                $a->acquisition_date?->format('d/m/Y') ?? '',
                number_format((float) $a->purchase_price, 0, ',', ''),
                number_format((float) $a->additional_fees, 0, ',', ''),
                number_format((float) $a->acquisition_value, 0, ',', ''),
                number_format((float) $a->accumulated_depreciation, 0, ',', ''),
                number_format((float) $a->net_book_value, 0, ',', ''),
                number_format((float) $a->total_usage_revenue, 0, ',', ''),
                number_format((float) $a->total_rental_revenue, 0, ',', ''),
                number_format((float) $a->total_revenue_generated, 0, ',', ''),
                number_format((float) $a->total_maintenance_costs, 0, ',', ''),
                number_format((float) $a->net_profitability, 0, ',', ''),
                number_format((float) $a->roi_percentage, 1, ',', '')
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="immobilisations_ivosphere_'.date('Y-m-d').'.csv"',
        ]);
    }
}
