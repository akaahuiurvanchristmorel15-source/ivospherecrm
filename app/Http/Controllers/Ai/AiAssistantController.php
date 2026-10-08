<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiAssistantService $aiService
    ) {}

    public function index(): View
    {
        $domains = Domain::all();

        return view('ai.index', [
            'domains' => $domains,
            'defaultSuggestions' => [
                'Donne-moi le chiffre d\'affaires de TECH ce mois-ci',
                'Quels produits sont bientôt en rupture ?',
                'Quelles factures sont en retard ?',
                'Quels clients n\'ont pas commandé depuis 3 mois ?',
                'Résume les ventes de cette semaine',
                'Combien avons-nous de trésorerie disponible ?',
            ],
        ]);
    }

    public function query(Request $request): JsonResponse|View|RedirectResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->aiService->processQuery($validated['query']);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('ai.index')->with([
            'aiQuery' => $validated['query'],
            'aiResponse' => $result,
        ]);
    }

    public function generate(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'topic' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string'],
            'target' => ['nullable', 'string'],
            'tone' => ['nullable', 'string'],
        ]);

        $generated = $this->aiService->generateContent($validated['type'], $validated);

        if ($request->wantsJson()) {
            return response()->json($generated);
        }

        return redirect()->route('ai.index')->with([
            'generatedContent' => $generated,
        ]);
    }
}
