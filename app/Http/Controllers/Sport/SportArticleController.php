<?php

namespace App\Http\Controllers\Sport;

use App\Http\Controllers\Controller;
use App\Models\SportArticle;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SportArticleController extends Controller
{
    public function index(Request $request)
    {
        $articles = SportArticle::with(['customer'])->latest()->paginate(15);

        return view('sport.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('sport.articles.create_edit');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'size' => 'required|string',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $validated['total'] = $validated['quantity'] * $validated['unit_price'];

        $article = SportArticle::create($validated);
        ActivityLogger::log('create', 'Création d\'un article de sport', $article);

        return redirect()->route('sport.articles.index')->with('success', 'Article créé avec succès.');
    }

    public function edit(SportArticle $article)
    {
        return view('sport.articles.create_edit', compact('article'));
    }

    public function update(Request $request, SportArticle $article)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'size' => 'required|string',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'status' => 'required|string',
        ]);

        $validated['total'] = $validated['quantity'] * $validated['unit_price'];

        $article->update($validated);
        ActivityLogger::log('update', 'Mise à jour d\'un article de sport', $article);

        return redirect()->route('sport.articles.index')->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy(SportArticle $article)
    {
        ActivityLogger::log('delete', 'Suppression d\'un article de sport', $article);
        $article->delete();

        return redirect()->route('sport.articles.index')->with('success', 'Article supprimé avec succès.');
    }
}
