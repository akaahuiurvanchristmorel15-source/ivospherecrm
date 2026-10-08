<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display general and financial settings along with domains management.
     */
    public function index(): View
    {
        $settings = Setting::all()->keyBy('key');
        $domains = Domain::withCount('users')->orderBy('name')->get();
        $poleShowcaseImages = Setting::get('pole_showcase_images', []);

        return view('admin.settings.index', compact('settings', 'domains', 'poleShowcaseImages'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_legal_form' => ['nullable', 'string', 'max:100'],
            'company_capital' => ['nullable', 'string', 'max:100'],
            'company_email' => ['required', 'email', 'max:255'],
            'company_phone' => ['required', 'string', 'max:50'],
            'company_address' => ['required', 'string', 'max:255'],
            'company_postal_box' => ['nullable', 'string', 'max:100'],

            // Codes Administratifs & Identifiants Fiscaux
            'company_rccm' => ['nullable', 'string', 'max:100'],
            'company_cc' => ['nullable', 'string', 'max:100'],
            'company_tax_regime' => ['nullable', 'string', 'max:100'],
            'company_tax_center' => ['nullable', 'string', 'max:150'],
            'company_cnps' => ['nullable', 'string', 'max:100'],

            // Coordonnées Bancaires Officielles
            'company_bank_name' => ['nullable', 'string', 'max:150'],
            'company_bank_rib' => ['nullable', 'string', 'max:150'],

            // Paramètres Financiers
            'currency' => ['required', 'string', 'max:20'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'fiscal_year' => ['required', 'string', 'max:20'],
        ]);

        foreach ($validated as $key => $value) {
            $group = in_array($key, ['currency', 'default_tax_rate', 'fiscal_year']) ? 'finance' : 'general';
            $type = $key === 'default_tax_rate' ? 'integer' : 'string';

            Setting::set($key, $value ?? '', $group, $type);
        }

        ActivityLogger::log(
            action: 'mise_a_jour_parametres',
            description: 'A mis à jour les paramètres généraux et financiers de l\'ERP'
        );

        return redirect()->route('admin.settings.index')
            ->with('success', 'Les paramètres système ont été enregistrés avec succès.');
    }

    /**
     * Update showcase images (3 per pole) for the landing page.
     */
    public function updatePoleImages(Request $request): RedirectResponse
    {
        $request->validate([
            'images' => ['nullable', 'array'],
            'images.*.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'titles' => ['nullable', 'array'],
            'titles.*.*' => ['nullable', 'string', 'max:120'],
        ]);

        $existing = Setting::get('pole_showcase_images', []);
        if (! is_array($existing)) {
            $existing = [];
        }

        $poles = ['print', 'sport', 'tech', 'media', 'assurance'];

        foreach ($poles as $pole) {
            $existing[$pole] = $existing[$pole] ?? [];
            for ($i = 0; $i < 3; $i++) {
                $existing[$pole][$i] = $existing[$pole][$i] ?? [
                    'title' => '',
                    'image' => null,
                ];

                if ($request->has("titles.$pole.$i")) {
                    $existing[$pole][$i]['title'] = (string) $request->input("titles.$pole.$i");
                }

                if ($request->hasFile("images.$pole.$i")) {
                    $path = $request->file("images.$pole.$i")->store('poles', 'public');
                    $existing[$pole][$i]['image'] = 'storage/'.$path;
                }
            }
        }

        Setting::set('pole_showcase_images', $existing, 'showcase', 'json');

        ActivityLogger::log(
            action: 'mise_a_jour_images_poles',
            description: 'A mis à jour les visuels de la page d\'accueil pour les 5 pôles de services'
        );

        return redirect()->route('admin.settings.index')
            ->with('success', 'Les visuels des 5 pôles de la page d\'accueil ont été enregistrés avec succès.');
    }
}
