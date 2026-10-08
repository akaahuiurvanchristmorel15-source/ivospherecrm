<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\CustomWorkflow;
use App\Models\Role;
use App\Models\WorkflowStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    public function index(): View
    {
        $rules = AutomationRule::orderByDesc('is_active')->orderBy('name')->get();
        $workflows = CustomWorkflow::with(['steps.role'])->orderByDesc('is_active')->get();
        $roles = Role::all();

        return view('automations.index', compact('rules', 'workflows', 'roles'));
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
            'actions' => ['nullable', 'string'],
        ]);

        AutomationRule::create([
            'name' => $validated['name'],
            'trigger_event' => $validated['trigger_event'],
            'description' => $validated['description'] ?? null,
            'conditions' => ! empty($validated['conditions']) ? (is_array($validated['conditions']) ? $validated['conditions'] : json_decode($validated['conditions'], true)) : null,
            'actions' => ! empty($validated['actions']) ? (is_array($validated['actions']) ? $validated['actions'] : json_decode($validated['actions'], true)) : null,
            'is_active' => true,
        ]);

        return redirect()->route('automations.index')->with('success', 'Règle d\'automatisation créée avec succès.');
    }

    public function toggleRule(AutomationRule $rule): RedirectResponse
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        $status = $rule->is_active ? 'activée' : 'désactivée';

        return back()->with('info', "Règle « {$rule->name} » {$status}.");
    }

    public function storeWorkflow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.name' => ['required', 'string'],
            'steps.*.role_id' => ['nullable', 'exists:roles,id'],
            'steps.*.time_limit_hours' => ['nullable', 'integer', 'min:1'],
        ]);

        $workflow = CustomWorkflow::create([
            'name' => $validated['name'],
            'module' => $validated['module'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        foreach ($validated['steps'] as $index => $stepData) {
            WorkflowStep::create([
                'workflow_id' => $workflow->id,
                'step_order' => $index + 1,
                'name' => $stepData['name'],
                'role_id' => $stepData['role_id'] ?? null,
                'time_limit_hours' => $stepData['time_limit_hours'] ?? null,
            ]);
        }

        return redirect()->route('automations.index')->with('success', "Workflow « {$workflow->name} » créé avec ses étapes d'approbation.");
    }

    public function toggleWorkflow(CustomWorkflow $workflow): RedirectResponse
    {
        $workflow->update(['is_active' => ! $workflow->is_active]);

        $status = $workflow->is_active ? 'activé' : 'désactivé';

        return back()->with('info', "Circuit d'approbation « {$workflow->name} » {$status}.");
    }
}
