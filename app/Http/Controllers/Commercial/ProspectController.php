<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\Prospect;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProspectController extends Controller
{
    public const STAGES = [
        'nouveau' => ['label' => 'Nouveau', 'color' => 'slate', 'default_prob' => 10],
        'contacte' => ['label' => 'Contacté', 'color' => 'blue', 'default_prob' => 25],
        'interesse' => ['label' => 'Intéressé', 'color' => 'indigo', 'default_prob' => 50],
        'devis_envoye' => ['label' => 'Devis envoyé', 'color' => 'amber', 'default_prob' => 70],
        'negociation' => ['label' => 'Négociation', 'color' => 'purple', 'default_prob' => 85],
        'gagne' => ['label' => 'Gagné (Conclu)', 'color' => 'emerald', 'default_prob' => 100],
        'perdu' => ['label' => 'Perdu', 'color' => 'rose', 'default_prob' => 0],
    ];

    public function index(Request $request)
    {
        $query = Prospect::with(['assignedUser', 'commercial', 'domain']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        if ($request->filled('commercial_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('commercial_id', $request->commercial_id)
                    ->orWhere('assigned_to', $request->commercial_id);
            });
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        $allProspects = $query->latest()->get();

        // Organize by Kanban stage
        $kanban = [];
        foreach (self::STAGES as $key => $info) {
            $stageProspects = $allProspects->filter(function ($p) use ($key) {
                return ($p->stage ?? 'nouveau') === $key;
            });

            $kanban[$key] = [
                'info' => $info,
                'prospects' => $stageProspects,
                'count' => $stageProspects->count(),
                'total_value' => $stageProspects->sum('estimated_value'),
            ];
        }

        // Summary metrics
        $totalPipelineValue = $allProspects->sum('estimated_value');
        $weightedPipelineValue = $allProspects->sum(function ($p) {
            return ($p->estimated_value ?? 0) * (($p->probability ?? 0) / 100);
        });
        $totalCount = $allProspects->count();
        $wonCount = $allProspects->where('stage', 'gagne')->count();
        $wonValue = $allProspects->where('stage', 'gagne')->sum('estimated_value');

        $domains = Domain::active()->get();
        $commercials = User::active()->get();
        $viewMode = $request->get('view', 'kanban'); // 'kanban' or 'list'

        return view('commercial.prospects.index', compact(
            'kanban',
            'allProspects',
            'totalPipelineValue',
            'weightedPipelineValue',
            'totalCount',
            'wonCount',
            'wonValue',
            'domains',
            'commercials',
            'viewMode'
        ));
    }

    public function create()
    {
        $domains = Domain::active()->get();
        $commercials = User::active()->get();
        $stages = self::STAGES;

        return view('commercial.prospects.create_edit', compact('domains', 'commercials', 'stages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'assigned_to' => 'nullable|exists:users,id',
            'commercial_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:100',
            'stage' => 'nullable|in:nouveau,contacte,interesse,devis_envoye,negociation,gagne,perdu',
            'status' => 'nullable|string|max:50',
            'probability' => 'nullable|integer|min:0|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $stage = $validated['stage'] ?? 'nouveau';
        $validated['stage'] = $stage;
        $validated['probability'] = $validated['probability'] ?? (self::STAGES[$stage]['default_prob'] ?? 10);
        $validated['commercial_id'] = $validated['commercial_id'] ?? $validated['assigned_to'] ?? auth()->id();
        $validated['assigned_to'] = $validated['commercial_id'];
        $validated['status'] = in_array($stage, ['gagne', 'perdu']) ? $stage : 'en_cours';

        $prospect = Prospect::create($validated);
        ActivityLogger::log('created_prospect', 'Création du prospect '.$prospect->name.' (Étape : '.self::STAGES[$stage]['label'].')', $prospect);

        return redirect()->route('commercial.prospects.index')->with('success', 'Prospect créé avec succès.');
    }

    public function edit(Prospect $prospect)
    {
        $domains = Domain::active()->get();
        $commercials = User::active()->get();
        $stages = self::STAGES;

        return view('commercial.prospects.create_edit', compact('prospect', 'domains', 'commercials', 'stages'));
    }

    public function update(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'domain_id' => 'nullable|exists:domains,id',
            'assigned_to' => 'nullable|exists:users,id',
            'commercial_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:100',
            'stage' => 'nullable|in:nouveau,contacte,interesse,devis_envoye,negociation,gagne,perdu',
            'status' => 'nullable|string|max:50',
            'probability' => 'nullable|integer|min:0|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['stage'])) {
            $stage = $validated['stage'];
            $validated['probability'] = $validated['probability'] ?? (self::STAGES[$stage]['default_prob'] ?? 10);
            $validated['status'] = in_array($stage, ['gagne', 'perdu']) ? $stage : 'en_cours';
        }

        if (isset($validated['commercial_id'])) {
            $validated['assigned_to'] = $validated['commercial_id'];
        }

        $prospect->update($validated);
        ActivityLogger::log('updated_prospect', 'Modification du prospect '.$prospect->name, $prospect);

        return redirect()->route('commercial.prospects.index')->with('success', 'Prospect mis à jour avec succès.');
    }

    public function updateStage(Request $request, Prospect $prospect)
    {
        $validated = $request->validate([
            'stage' => 'required|in:nouveau,contacte,interesse,devis_envoye,negociation,gagne,perdu',
            'probability' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $stage = $validated['stage'];
        $probability = $validated['probability'] ?? (self::STAGES[$stage]['default_prob'] ?? 10);
        $status = in_array($stage, ['gagne', 'perdu']) ? $stage : 'en_cours';

        $notes = $prospect->notes;
        if (! empty($validated['notes'])) {
            $notes = ($notes ? $notes."\n" : '').'['.now()->format('d/m/Y H:i').'] Étape: '.self::STAGES[$stage]['label'].' — '.$validated['notes'];
        }

        $prospect->update([
            'stage' => $stage,
            'probability' => $probability,
            'status' => $status,
            'notes' => $notes,
        ]);

        ActivityLogger::log('pipeline_stage_changed', 'Changement d\'étape du prospect '.$prospect->name.' vers '.self::STAGES[$stage]['label'], $prospect);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Étape mise à jour avec succès.',
                'prospect' => $prospect,
            ]);
        }

        return back()->with('success', 'Étape du prospect mise à jour vers '.self::STAGES[$stage]['label'].'.');
    }

    public function destroy(Prospect $prospect)
    {
        $name = $prospect->name;
        $prospect->delete();
        ActivityLogger::log('deleted_prospect', 'Suppression du prospect '.$name, null);

        return redirect()->route('commercial.prospects.index')->with('success', 'Prospect supprimé avec succès.');
    }

    public function convertToCustomer(Prospect $prospect)
    {
        $customer = Customer::create([
            'domain_id' => $prospect->domain_id,
            'commercial_id' => $prospect->commercial_id ?? $prospect->assigned_to ?? auth()->id(),
            'user_id' => auth()->id(),
            'code' => 'CLI-'.Str::upper(Str::random(6)),
            'type' => $prospect->company ? 'entreprise' : 'particulier',
            'category' => 'standard',
            'name' => $prospect->name,
            'company' => $prospect->company,
            'email' => $prospect->email,
            'phone' => $prospect->phone,
            'whatsapp' => $prospect->phone,
            'status' => 'actif',
            'loyalty_points' => 0,
            'loyalty_level' => 'BRONZE',
            'notes' => 'Converti depuis le prospect '.$prospect->name.".\n".$prospect->notes,
        ]);

        $prospect->update([
            'stage' => 'gagne',
            'status' => 'gagné',
            'probability' => 100,
        ]);

        ActivityLogger::log('converted_prospect', 'Conversion du prospect '.$prospect->name.' en client ('.$customer->code.')', $customer);

        return redirect()->route('commercial.customers.show', $customer)->with('success', 'Prospect converti en client avec succès ! Profil 360° généré.');
    }
}
