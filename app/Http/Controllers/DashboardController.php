<?php

namespace App\Http\Controllers;

use App\Services\AppNavigator;
use App\Services\GeneralAssistantService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Accueil : assistant IA général. Les liens filtrés (pôle, période) ouvrent les indicateurs.
     */
    public function index(Request $request, GeneralAssistantService $assistant, AppNavigator $navigator): View
    {
        if ($request->hasAny(['domain', 'period', 'date_from', 'date_to'])) {
            return $this->indicators($request);
        }

        $user = $request->user();

        return view('dashboard.assistant', [
            'firstName' => explode(' ', trim((string) $user->name))[0],
            'greeting' => match (true) {
                now()->hour < 12 => 'Bonjour',
                now()->hour < 18 => 'Bon après-midi',
                default => 'Bonsoir',
            },
            'canUseAssistant' => $user->hasPermission('ai.assistant'),
            'suggestions' => $assistant->suggestionsFor($user),
            'modules' => $navigator->pagesFor($user)
                ->groupBy('module')
                ->map(fn ($pages) => $pages->map(fn (array $page) => ['label' => $page['label'], 'url' => $page['url']])->values())
                ->sortKeys(),
        ]);
    }

    /**
     * Indicateurs temps réel du tableau de bord central.
     */
    public function indicators(Request $request): View
    {
        return view('dashboard.index', [
            'filters' => $request->only(['domain', 'period', 'date_from', 'date_to']),
        ]);
    }
}
