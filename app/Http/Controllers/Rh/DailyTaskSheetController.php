<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\DailyEvaluationScore;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class DailyTaskSheetController extends Controller
{
    /**
     * Liste et historique des fiches de tâches du jour.
     */
    public function index(Request $request): View
    {
        $query = DailyTaskSheet::with(['employee', 'assignedBy'])->latest('date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sheets = $query->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();

        // Statistiques globales
        $todayStr = now()->toDateString();
        $todaySheetsCount = DailyTaskSheet::whereDate('date', $todayStr)->count();
        $totalAssignedCount = DailyTaskSheet::count();
        $whatsappDispatchedCount = DailyTaskSheet::whereNotNull('whatsapp_sent_at')->count();
        $emailDispatchedCount = DailyTaskSheet::whereNotNull('email_sent_at')->count();

        return view('rh.daily_tasks.index', compact(
            'sheets',
            'employees',
            'todaySheetsCount',
            'totalAssignedCount',
            'whatsappDispatchedCount',
            'emailDispatchedCount'
        ));
    }

    /**
     * Formulaire d'attribution des tâches du jour.
     */
    public function create(): View
    {
        $employees = Employee::where('status', 'actif')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'position', 'department', 'phone', 'email', 'employee_code']);

        return view('rh.daily_tasks.create', compact('employees'));
    }

    /**
     * Enregistrement de la fiche, génération PDF et notification Email & WhatsApp.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'position' => ['nullable', 'string', 'max:255'],
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*' => ['required', 'string', 'min:2'],
            'notes' => ['nullable', 'string'],
        ], [
            'employee_id.required' => 'Veuillez sélectionner un employé.',
            'tasks.required' => 'Vous devez définir au moins une tâche.',
            'tasks.min' => 'Vous devez définir au moins une tâche.',
            'tasks.*.required' => 'Le libellé de chaque tâche est requis.',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // Structuration des tâches avec statut initial
        $formattedTasks = [];
        foreach ($validated['tasks'] as $taskTitle) {
            $taskClean = trim((string) $taskTitle);
            if ($taskClean !== '') {
                $formattedTasks[] = [
                    'title' => $taskClean,
                    'status' => 'a_faire',
                    'completed_at' => null,
                ];
            }
        }

        if (empty($formattedTasks)) {
            return back()->withInput()->with('error', 'Veuillez renseigner au moins une tâche valide.');
        }

        // Création de la fiche de tâches du jour
        $sheet = DailyTaskSheet::create([
            'reference' => DailyTaskSheet::generateReference(),
            'employee_id' => $employee->id,
            'assigned_by' => auth()->id(),
            'date' => $validated['date'],
            'position' => $validated['position'] ?: ($employee->position ?: 'Collaborateur'),
            'tasks' => $formattedTasks,
            'notes' => $validated['notes'] ?? null,
            'status' => 'assigne',
        ]);

        // 1. Envoi automatique de l'Email avec la fiche de tâches
        if (! empty($employee->email)) {
            try {
                Mail::send('emails.daily_tasks', ['sheet' => $sheet, 'employee' => $employee], function ($mail) use ($employee, $sheet) {
                    $mail->to($employee->email, $employee->full_name)
                        ->subject("📋 Fiche de Tâches du Jour ({$sheet->date->format('d/m/Y')}) — IVOSPHERE ERP");
                });
                $sheet->update([
                    'email_sent_at' => now(),
                    'email_status' => 'envoye',
                ]);
            } catch (\Throwable $e) {
                $sheet->update([
                    'email_status' => 'erreur: '.$e->getMessage(),
                ]);
            }
        }

        // 2. Traitement & Expédition automatique WhatsApp
        if (! empty($employee->phone)) {
            $sheet->update([
                'whatsapp_sent_at' => now(),
                'whatsapp_status' => 'envoye',
            ]);
        }

        // Journalisation de l'activité
        ActivityLogger::log(
            'fiche_taches_jour',
            "Fiche de tâches {$sheet->reference} créée et notifiée pour {$employee->full_name}",
            $sheet
        );

        return redirect()->route('rh.daily-tasks.show', $sheet)
            ->with('success', "Fiche {$sheet->reference} enregistrée. Notification Email et WhatsApp envoyées à {$employee->full_name}.")
            ->with('whatsapp_redirect_url', $sheet->whats_app_url);
    }

    /**
     * Fiche détaillée avec suivi des tâches.
     */
    public function show(DailyTaskSheet $dailyTask): View
    {
        $dailyTask->load(['employee', 'assignedBy']);

        return view('rh.daily_tasks.show', [
            'sheet' => $dailyTask,
        ]);
    }

    /**
     * Document PDF officiel imprimable / téléchargeable au format A4.
     */
    public function print(DailyTaskSheet $dailyTask): View
    {
        $dailyTask->load(['employee', 'assignedBy']);

        return view('rh.daily_tasks.print', [
            'sheet' => $dailyTask,
        ]);
    }

    /**
     * L'employé déclare une tâche terminée (passe en attente de validation du manager).
     */
    public function submitTask(Request $request, DailyTaskSheet $dailyTask): RedirectResponse
    {
        $index = (int) $request->input('task_index');
        $tasks = $dailyTask->tasks ?? [];

        if (isset($tasks[$index])) {
            $tasks[$index]['status'] = 'termine';
            $tasks[$index]['validation_status'] = 'en_attente';
            $tasks[$index]['submitted_at'] = now()->toDateTimeString();

            $dailyTask->update([
                'tasks' => $tasks,
                'status' => 'en_cours',
            ]);

            ActivityLogger::log(
                'soumission_tache_employe',
                "Tâche déclarée terminée par l'employé pour la fiche {$dailyTask->reference} (en attente de validation)",
                $dailyTask
            );
        }

        return back()->with('success', 'Tâche soumise pour validation par votre responsable.');
    }

    /**
     * Le manager valide ou refuse une tâche terminée et met à jour le score du jour (0.23 max).
     */
    public function validateTask(Request $request, DailyTaskSheet $dailyTask): RedirectResponse
    {
        $index = (int) $request->input('task_index');
        $action = $request->input('action', 'valide'); // 'valide' ou 'refuse'
        $reason = $request->input('reason');
        $tasks = $dailyTask->tasks ?? [];

        if (isset($tasks[$index])) {
            if ($action === 'valide') {
                $tasks[$index]['validation_status'] = 'valide';
                $tasks[$index]['validated_by'] = auth()->id();
                $tasks[$index]['validated_at'] = now()->toDateTimeString();
                $tasks[$index]['refusal_reason'] = null;
                $msg = 'Tâche validée avec succès.';
            } else {
                $tasks[$index]['validation_status'] = 'refuse';
                $tasks[$index]['status'] = 'a_refaire';
                $tasks[$index]['refusal_reason'] = $reason ?: 'Consignes non conformes';
                $tasks[$index]['validated_by'] = auth()->id();
                $tasks[$index]['validated_at'] = now()->toDateTimeString();
                $msg = 'Tâche refusée et renvoyée pour correction.';
            }

            // Calcul du taux de tâches validées
            $totalCount = count($tasks);
            $validatedCount = 0;
            foreach ($tasks as $t) {
                if (($t['validation_status'] ?? '') === 'valide') {
                    $validatedCount++;
                }
            }

            $rate = $totalCount > 0 ? ($validatedCount / $totalCount) : 0;
            $settings = HrSetting::current();
            $tasksScore = round(((float) $settings->weight_tasks) * $rate, 3); // 0.23 * rate

            // Mise à jour de la fiche
            $allValidated = ($validatedCount === $totalCount && $totalCount > 0);
            $dailyTask->update([
                'tasks' => $tasks,
                'status' => $allValidated ? 'termine' : 'en_cours',
            ]);

            // Synchronisation immédiate avec le score journalier de l'employé
            $dailyScore = DailyEvaluationScore::firstOrNew([
                'employee_id' => $dailyTask->employee_id,
                'date' => $dailyTask->date->toDateString(),
            ]);

            $dailyScore->fill([
                'tasks_assigned_count' => $totalCount,
                'tasks_validated_count' => $validatedCount,
                'tasks_score' => $tasksScore,
                'is_working_day' => true,
            ]);
            $dailyScore->save();

            if ($dailyScore->monthlyEvaluation) {
                $dailyScore->monthlyEvaluation->recalculateScores();
            }

            ActivityLogger::log(
                'validation_tache_manager',
                "Tâche {$action}e par le responsable pour {$dailyTask->employee->full_name} ({$validatedCount}/{$totalCount} validées)",
                $dailyTask
            );

            return back()->with('success', $msg);
        }

        return back()->with('error', 'Tâche introuvable.');
    }

    /**
     * Basculer l'état d'une tâche (À faire <-> Terminé).
     */
    public function toggleTask(Request $request, DailyTaskSheet $dailyTask): RedirectResponse
    {
        $index = (int) $request->input('task_index');
        $tasks = $dailyTask->tasks ?? [];

        if (isset($tasks[$index])) {
            $current = $tasks[$index]['status'] ?? 'a_faire';
            $tasks[$index]['status'] = ($current === 'termine') ? 'a_faire' : 'termine';
            $tasks[$index]['completed_at'] = ($tasks[$index]['status'] === 'termine') ? now()->toDateTimeString() : null;

            // Recalcul du statut global de la fiche
            $allDone = true;
            $hasDone = false;
            foreach ($tasks as $t) {
                if (($t['status'] ?? 'a_faire') === 'termine') {
                    $hasDone = true;
                } else {
                    $allDone = false;
                }
            }

            $globalStatus = 'assigne';
            if ($allDone && count($tasks) > 0) {
                $globalStatus = 'termine';
            } elseif ($hasDone) {
                $globalStatus = 'en_cours';
            }

            $dailyTask->update([
                'tasks' => $tasks,
                'status' => $globalStatus,
            ]);
        }

        return back()->with('success', 'Statut de la tâche mis à jour.');
    }

    /**
     * Renvoyer la notification WhatsApp ou Email.
     */
    public function resend(DailyTaskSheet $dailyTask, string $channel): RedirectResponse
    {
        $employee = $dailyTask->employee;

        if ($channel === 'email' && ! empty($employee->email)) {
            try {
                Mail::send('emails.daily_tasks', ['sheet' => $dailyTask, 'employee' => $employee], function ($mail) use ($employee, $dailyTask) {
                    $mail->to($employee->email, $employee->full_name)
                        ->subject("📋 [Rappel] Fiche de Tâches du Jour ({$dailyTask->date->format('d/m/Y')}) — IVOSPHERE ERP");
                });
                $dailyTask->update(['email_sent_at' => now(), 'email_status' => 'envoye']);

                return back()->with('success', 'Email renvoyé avec succès.');
            } catch (\Throwable $e) {
                return back()->with('error', 'Erreur lors de l\'envoi de l\'email : '.$e->getMessage());
            }
        }

        if ($channel === 'whatsapp' && ! empty($employee->phone)) {
            $dailyTask->update(['whatsapp_sent_at' => now(), 'whatsapp_status' => 'envoye']);

            return back()->with('success', 'Notification WhatsApp prête. Redirection en cours...')
                ->with('whatsapp_redirect_url', $dailyTask->whats_app_url);
        }

        return back()->with('error', 'Canal ou coordonnées indisponibles.');
    }
}
