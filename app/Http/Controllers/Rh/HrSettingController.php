<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\HrSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrSettingController extends Controller
{
    /**
     * Affiche l'écran des paramètres RH configurables.
     */
    public function index(): View
    {
        $settings = HrSetting::current();

        return view('rh.settings.index', compact('settings'));
    }

    /**
     * Met à jour les règles et paramètres de gestion RH.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_start_time' => ['required', 'string', 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/'],
            'work_end_time' => ['required', 'string', 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/'],
            'auto_checkout_enabled' => ['nullable', 'boolean'],
            'auto_checkout_time' => ['required', 'string', 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/'],
            'geofence_enabled' => ['nullable', 'boolean'],
            'office_latitude' => ['required', 'numeric', 'between:-90,90'],
            'office_longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'max_work_days_per_week' => ['required', 'integer', 'min:1', 'max:6'],
            'allow_custom_schedules' => ['nullable', 'boolean'],
            'evaluation_day_of_month' => ['required', 'integer', 'min:1', 'max:28'],
            'weight_punctuality' => ['required', 'numeric', 'min:0', 'max:1'],
            'weight_tasks' => ['required', 'numeric', 'min:0', 'max:1'],
            'weight_teamwork' => ['required', 'numeric', 'min:0', 'max:1'],
            'max_evaluation_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'punctuality_grace_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'late_threshold_minutes' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        $settings = HrSetting::current();

        $settings->update([
            'work_start_time' => $validated['work_start_time'],
            'work_end_time' => $validated['work_end_time'],
            'auto_checkout_enabled' => $request->boolean('auto_checkout_enabled'),
            'auto_checkout_time' => $validated['auto_checkout_time'],
            'geofence_enabled' => $request->boolean('geofence_enabled'),
            'office_latitude' => $validated['office_latitude'],
            'office_longitude' => $validated['office_longitude'],
            'geofence_radius_meters' => (int) $validated['geofence_radius_meters'],
            'max_work_days_per_week' => (int) $validated['max_work_days_per_week'],
            'allow_custom_schedules' => $request->boolean('allow_custom_schedules'),
            'evaluation_day_of_month' => (int) $validated['evaluation_day_of_month'],
            'weight_punctuality' => $validated['weight_punctuality'],
            'weight_tasks' => $validated['weight_tasks'],
            'weight_teamwork' => $validated['weight_teamwork'],
            'max_evaluation_score' => $validated['max_evaluation_score'],
            'punctuality_grace_minutes' => (int) $validated['punctuality_grace_minutes'],
            'late_threshold_minutes' => (int) $validated['late_threshold_minutes'],
        ]);

        ActivityLogger::log(
            'modification_parametres_rh',
            'Mise à jour des paramètres RH généraux (horaires, géolocalisation, barème évaluation)',
            $settings
        );

        return redirect()->route('rh.settings.index')
            ->with('success', 'Paramètres RH enregistrés avec succès.');
    }

    /**
     * Régénère le jeton secret pour le QR code dynamique des locaux.
     */
    public function regenerateQrSecret(): RedirectResponse
    {
        $settings = HrSetting::current();
        $settings->update([
            'dynamic_qr_secret' => bin2hex(random_bytes(16)),
        ]);

        ActivityLogger::log(
            'regeneration_qr_secret_rh',
            'Régénération de la clé secrète du QR code dynamique de pointage',
            $settings
        );

        return back()->with('success', 'Nouvelle clé secrète de pointage générée.');
    }
}
