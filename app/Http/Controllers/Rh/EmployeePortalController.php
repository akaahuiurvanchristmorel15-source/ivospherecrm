<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DailyEvaluationScore;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\LeaveRequest;
use App\Models\MonthlyEvaluation;
use App\Models\WorkSchedule;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    /**
     * Espace Collaborateur Mobile & Desktop : Pointage, Tâches du Jour, Planning et Évaluation /30.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $settings = HrSetting::current();
        $today = now()->toDateString();

        // 1. Résolution de l'employé connecté
        $employee = $user->employee ?? Employee::where('email', $user->email)->first();

        // Si l'utilisateur est admin ou responsable RH, on lui permet de tester l'espace pour n'importe quel collaborateur
        $allEmployees = null;
        if ($user->hasRole('administrateur', 'responsable', 'responsable_rh')) {
            $allEmployees = Employee::where('status', 'actif')->orderBy('first_name')->get();
            if ($request->filled('employee_id')) {
                $employee = Employee::find($request->employee_id) ?? $employee;
            }
        }

        // Si aucun employé n'est rattaché (ex: premier test admin), prendre le premier actif
        if (! $employee && $allEmployees && $allEmployees->isNotEmpty()) {
            $employee = $allEmployees->first();
        }

        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Aucune fiche collaborateur associée à votre compte utilisateur.');
        }

        // 2. Pointage du jour
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 3. Fiche de tâches du jour
        $todayTaskSheet = DailyTaskSheet::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        // 4. Planning hebdomadaire en cours
        $currentSchedule = WorkSchedule::with('days')
            ->where('employee_id', $employee->id)
            ->active()
            ->orderByDesc('week_start_date')
            ->first();

        // 5. Score journalier d'évaluation
        $todayScore = DailyEvaluationScore::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // 6. Évaluation mensuelle en cours (/30)
        $currentEvaluation = MonthlyEvaluation::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        // 7. Historique des évaluations passées
        $pastEvaluations = MonthlyEvaluation::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get();

        // 8. Demandes de congés & permissions récentes
        $recentLeaves = $employee->leaveRequests()
            ->latest()
            ->take(4)
            ->get();

        return view('rh.portal.index', compact(
            'employee',
            'settings',
            'todayAttendance',
            'todayTaskSheet',
            'currentSchedule',
            'todayScore',
            'currentEvaluation',
            'pastEvaluations',
            'recentLeaves',
            'allEmployees'
        ));
    }

    /**
     * Dépôt d'une demande de congé ou permission par le collaborateur.
     */
    public function requestLeave(Request $request): RedirectResponse
    {
        $employee = auth()->user()->employee ?? Employee::where('email', auth()->user()->email)->first();
        if (! $employee) {
            abort(403, 'Compte collaborateur non trouvé.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:congé_annuel,congé_maladie,congé_maternité,sans_solde,autre'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['employee_id'] = $employee->id;
        $validated['status'] = 'en_attente';

        $leave = LeaveRequest::create($validated);

        ActivityLogger::log('demande_conge_espace_employe', "Demande de congé déposée par {$employee->full_name} ({$validated['days']} jour(s))", $leave);

        return back()->with('success', 'Votre demande de congé/permission a été transmise à la direction RH.');
    }

    /**
     * Soumission d'une tâche par le collaborateur connecté pour validation managériale.
     */
    public function submitTask(Request $request, DailyTaskSheet $dailyTask): RedirectResponse
    {
        $employee = auth()->user()->employee ?? Employee::where('email', auth()->user()->email)->first();

        // Contrôle d'autorisation
        if (! auth()->user()->hasRole('administrateur', 'responsable', 'responsable_rh') && (! $employee || $dailyTask->employee_id !== $employee->id)) {
            abort(403, 'Action non autorisée sur cette fiche de tâches.');
        }

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
                'soumission_tache_espace_employe',
                'Tâche #'.($index + 1)." soumise pour validation par {$dailyTask->employee->full_name}",
                $dailyTask
            );

            return back()->with('success', 'Tâche déclarée terminée ! Elle a été transmise à votre responsable pour validation.');
        }

        return back()->with('error', 'Tâche non trouvée.');
    }

    /**
     * Badge d'employé imprimable avec photo et QR Code personnel.
     */
    public function badge(Employee $employee): View
    {
        $settings = HrSetting::current();

        return view('rh.portal.badge', compact('employee', 'settings'));
    }
}
