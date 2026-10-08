<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
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
        return view('tech.projects.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

        $project = TechProject::create($validated);
        ActivityLogger::log('create', 'Création du projet tech', $project);

        return redirect()->route('tech.projects.index')->with('success', 'Projet créé avec succès.');
    }

    public function show(TechProject $project)
    {
        $project->load(['customer', 'tasks', 'user']);

        return view('tech.projects.show', compact('project'));
    }

    public function edit(TechProject $project)
    {
        return view('tech.projects.create_edit', compact('project'));
    }

    public function update(Request $request, TechProject $project)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string',
            'status' => 'required|string',
        ]);

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
