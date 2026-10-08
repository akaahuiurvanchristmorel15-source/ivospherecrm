<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\ScheduleDay;
use App\Models\WorkSchedule;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Matrice hebdomadaire de planification des équipes.
     */
    public function index(Request $request): View
    {
        $settings = HrSetting::current();

        // Détermine le lundi de la semaine sélectionnée
        if ($request->filled('week')) {
            $weekStart = Carbon::parse($request->week)->startOfWeek();
        } else {
            $weekStart = now()->startOfWeek();
        }

        $weekEnd = $weekStart->copy()->endOfWeek();
        $prevWeek = $weekStart->copy()->subWeek()->toDateString();
        $nextWeek = $weekStart->copy()->addWeek()->toDateString();

        // Construction des 7 jours de la semaine
        $weekDays = [];
        $dayNames = [
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
            7 => 'Dim',
        ];

        for ($i = 0; $i < 7; $i++) {
            $dayDate = $weekStart->copy()->addDays($i);
            $dayOfWeek = $i + 1; // 1 = Lundi ... 7 = Dimanche
            $weekDays[] = [
                'day_of_week' => $dayOfWeek,
                'short_name' => $dayNames[$dayOfWeek],
                'date' => $dayDate->toDateString(),
                'formatted' => $dayDate->format('d/m'),
                'is_today' => $dayDate->isToday(),
            ];
        }

        // Requête employés avec planning de cette semaine
        $employeesQuery = Employee::where('status', 'actif')
            ->with(['workSchedules' => function ($q) use ($weekStart) {
                $q->whereDate('week_start_date', $weekStart->toDateString())->with('days');
            }]);

        if ($request->filled('department')) {
            $employeesQuery->where('department', $request->department);
        }

        if ($request->filled('employee_id')) {
            $employeesQuery->where('id', $request->employee_id);
        }

        $employees = $employeesQuery->orderBy('first_name')->get();
        $departments = Employee::select('department')->whereNotNull('department')->distinct()->pluck('department');
        $allActiveEmployees = Employee::where('status', 'actif')->orderBy('first_name')->get();

        // Statistiques de la semaine
        $totalScheduledEmployees = $employees->filter(fn ($emp) => $emp->workSchedules->isNotEmpty())->count();
        $totalActive = $employees->count();
        $shiftsCount = 0;
        $restDaysCount = 0;

        foreach ($employees as $emp) {
            $schedule = $emp->workSchedules->first();
            if ($schedule) {
                $shiftsCount += $schedule->days->where('is_working_day', true)->count();
                $restDaysCount += $schedule->days->where('is_working_day', false)->count();
            }
        }

        return view('rh.schedules.index', compact(
            'employees',
            'weekStart',
            'weekEnd',
            'prevWeek',
            'nextWeek',
            'weekDays',
            'settings',
            'departments',
            'allActiveEmployees',
            'totalScheduledEmployees',
            'totalActive',
            'shiftsCount',
            'restDaysCount'
        ));
    }

    /**
     * Formulaire de création / édition du planning d'un collaborateur.
     */
    public function create(Request $request): View
    {
        $settings = HrSetting::current();
        $employeeId = $request->input('employee_id');
        $employee = $employeeId ? Employee::findOrFail($employeeId) : null;
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();

        $weekStart = $request->filled('week')
            ? Carbon::parse($request->week)->startOfWeek()
            : now()->startOfWeek();

        // Vérifier si un planning existe déjà pour cet employé cette semaine
        $existingSchedule = null;
        if ($employee) {
            $existingSchedule = WorkSchedule::where('employee_id', $employee->id)
                ->whereDate('week_start_date', $weekStart->toDateString())
                ->with('days')
                ->first();
        }

        $dayNames = [
            1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi',
            4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',
        ];

        $scheduleDaysData = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = $weekStart->copy()->addDays($i - 1);
            $existingDay = $existingSchedule ? $existingSchedule->days->firstWhere('day_of_week', $i) : null;

            $scheduleDaysData[$i] = [
                'day_of_week' => $i,
                'name' => $dayNames[$i],
                'date' => $date->toDateString(),
                'formatted' => $date->format('d/m/Y'),
                // Par défaut : Lundi à Samedi = Travail (1..6), Dimanche = Repos (7)
                'is_working_day' => $existingDay ? (bool) $existingDay->is_working_day : ($i <= 6),
                'start_time' => $existingDay ? $existingDay->start_time : $settings->work_start_time,
                'end_time' => $existingDay ? $existingDay->end_time : $settings->work_end_time,
                'notes' => $existingDay ? $existingDay->notes : null,
            ];
        }

        return view('rh.schedules.create_edit', compact(
            'employee',
            'employees',
            'weekStart',
            'settings',
            'existingSchedule',
            'scheduleDaysData'
        ));
    }

    /**
     * Enregistrement du planning hebdomadaire avec contrôle strict de la règle des 6 jours max.
     */
    public function store(Request $request): RedirectResponse
    {
        $settings = HrSetting::current();
        $maxAllowed = (int) $settings->max_work_days_per_week ?: 6;

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'week_start_date' => ['required', 'date'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.is_working_day' => ['nullable', 'boolean'],
            'days.*.start_time' => ['nullable', 'string'],
            'days.*.end_time' => ['nullable', 'string'],
            'days.*.notes' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $weekStart = Carbon::parse($validated['week_start_date'])->startOfWeek();

        // ── GARDE-FOU LÉGAL RH : VÉRIFICATION DE LA RÈGLE STRICTE DES 6 JOURS MAX ──
        $workingDaysCount = 0;
        foreach ($validated['days'] as $d) {
            if (! empty($d['is_working_day'])) {
                $workingDaysCount++;
            }
        }

        if ($workingDaysCount > $maxAllowed) {
            return back()->withInput()->with(
                'error',
                "⚠️ Impossible : un collaborateur ne peut pas être programmé plus de {$maxAllowed} jours sur une même semaine selon les paramètres RH d'IVOSPHERE (vous avez sélectionné {$workingDaysCount} jours). Veuillez définir au moins un jour de repos hebdomadaire."
            );
        }

        // Création ou mise à jour du planning hebdomadaire
        $schedule = WorkSchedule::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'week_start_date' => $weekStart->toDateString(),
            ],
            [
                'status' => 'valide',
                'total_working_days' => $workingDaysCount,
                'notes' => $request->input('notes'),
                'created_by' => auth()->id(),
            ]
        );

        // Synchronisation des 7 jours
        for ($i = 1; $i <= 7; $i++) {
            $dayData = $validated['days'][$i] ?? [];
            $date = $weekStart->copy()->addDays($i - 1);
            $isWorking = ! empty($dayData['is_working_day']);

            ScheduleDay::updateOrCreate(
                [
                    'work_schedule_id' => $schedule->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'day_of_week' => $i,
                    'is_working_day' => $isWorking,
                    'start_time' => $isWorking ? ($dayData['start_time'] ?? $settings->work_start_time) : null,
                    'end_time' => $isWorking ? ($dayData['end_time'] ?? $settings->work_end_time) : null,
                    'notes' => $dayData['notes'] ?? null,
                ]
            );
        }

        ActivityLogger::log(
            'enregistrement_planning',
            "Planning enregistré pour {$employee->full_name} — Semaine du {$weekStart->format('d/m/Y')} ({$workingDaysCount} jours travaillés)",
            $schedule
        );

        return redirect()->route('rh.schedules.index', ['week' => $weekStart->toDateString()])
            ->with('success', "Planning de {$employee->full_name} validé pour la semaine du {$weekStart->format('d/m/Y')} ({$workingDaysCount} jours ouvrés).");
    }

    /**
     * Application rapide du planning standard (Lun–Sam 8h–20h, Dim Repos) pour tous les collaborateurs actifs.
     */
    public function applyStandardToAll(Request $request): RedirectResponse
    {
        $settings = HrSetting::current();
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->week)->startOfWeek()
            : now()->startOfWeek();

        $employees = Employee::where('status', 'actif')->get();
        $count = 0;

        foreach ($employees as $employee) {
            $schedule = WorkSchedule::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'week_start_date' => $weekStart->toDateString(),
                ],
                [
                    'status' => 'valide',
                    'total_working_days' => 6,
                    'notes' => 'Planning standard d\'entreprise appliqué en lot (6 jours max).',
                    'created_by' => auth()->id(),
                ]
            );

            for ($i = 1; $i <= 7; $i++) {
                $date = $weekStart->copy()->addDays($i - 1);
                $isWorking = ($i <= 6); // Lundi à Samedi = Travail (6 jours), Dimanche = Repos

                ScheduleDay::updateOrCreate(
                    [
                        'work_schedule_id' => $schedule->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'day_of_week' => $i,
                        'is_working_day' => $isWorking,
                        'start_time' => $isWorking ? $settings->work_start_time : null,
                        'end_time' => $isWorking ? $settings->work_end_time : null,
                        'notes' => $isWorking ? null : 'Jour de repos hebdomadaire',
                    ]
                );
            }
            $count++;
        }

        ActivityLogger::log(
            'application_planning_standard_lot',
            "Application du planning standard (6 jours max) pour {$count} collaborateurs — Semaine du {$weekStart->format('d/m/Y')}",
            null
        );

        return redirect()->route('rh.schedules.index', ['week' => $weekStart->toDateString()])
            ->with('success', "Planning standard (6 jours de 8h à 20h, Dimanche repos) appliqué avec succès à {$count} collaborateurs.");
    }

    /**
     * Suppression d'un planning.
     */
    public function destroy(WorkSchedule $schedule): RedirectResponse
    {
        $empName = $schedule->employee?->full_name ?? 'Collaborateur';
        $weekStr = $schedule->week_start_date->format('d/m/Y');
        $schedule->delete();

        return back()->with('success', "Planning de {$empName} pour la semaine du {$weekStr} supprimé.");
    }
}
