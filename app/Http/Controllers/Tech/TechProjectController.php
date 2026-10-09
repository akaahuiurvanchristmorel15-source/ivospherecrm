<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\TechProject;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TechProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = TechProject::with(['customer', 'tasks'])->latest()->paginate(15);

        return view('tech.projects.index', compact('projects'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        return view('tech.projects.create_edit', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:tech_projects,reference',
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'status' => 'required|string|max:30',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id() ?? 1;
        $validated['domain_id'] = Domain::where('code', 'TECH')->value('id');
        $validated['budget'] = $validated['budget'] ?? 0;
        $validated['spent'] = $validated['spent'] ?? 0;
        $validated['progress'] = $validated['progress'] ?? 0;

        $project = TechProject::create($validated);
        ActivityLogger::log('create', 'Création du projet tech', $project);

        return redirect()->route('tech.projects.index')->with('success', 'Projet créé avec succès.');
    }

    public function show(TechProject $project)
    {
        $project->load(['customer', 'tasks.assignee', 'user']);

        return view('tech.projects.show', compact('project'));
    }

    public function edit(TechProject $project)
    {
        $customers = Customer::orderBy('name')->get();

        return view('tech.projects.create_edit', compact('project', 'customers'));
    }

    public function update(Request $request, TechProject $project)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:tech_projects,reference,'.$project->id,
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'status' => 'required|string|max:30',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'spent' => 'nullable|numeric|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['budget'] = $validated['budget'] ?? 0;
        $validated['spent'] = $validated['spent'] ?? 0;
        $validated['progress'] = $validated['progress'] ?? 0;

        $project->update($validated);
        ActivityLogger::log('update', 'Mise à jour du projet tech', $project);

        return redirect()->route('tech.projects.index')->with('success', 'Projet mis à jour avec succès.');
    }

    public function destroy(TechProject $project)
    {
        ActivityLogger::log('delete', 'Suppression du projet tech', $project);
        $project->delete();

        return redirect()->route('tech.projects.index')->with('success', 'Projet supprimé avec succès.');
    }
}
