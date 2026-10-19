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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Matrice hebdomadaire de planification des équipes.
     */
    public function index(Request $request): View
    {
        $settings = HrSetting::current();

        $weekStart = $this->resolveWeekStart($request->input('week'));

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

        $weekStart = $this->resolveWeekStart($request->input('week'));

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

        $oldDays = $request->old('days');
        $scheduleDaysData = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = $weekStart->copy()->addDays($i - 1);
            $existingDay = $existingSchedule ? $existingSchedule->days->firstWhere('day_of_week', $i) : null;

            $scheduleDaysData[$i] = [
                'day_of_week' => $i,
                'name' => $dayNames[$i],
                'date' => $date->toDateString(),
                'formatted' => $date->format('d/m/Y'),
                // Par défaut : semaine standard définie par les paramètres RH (jours max, horaires)
                'is_working_day' => $existingDay ? (bool) $existingDay->is_working_day : $settings->isStandardWorkingDay($i),
                'start_time' => $existingDay?->start_time ?: $settings->work_start_time,
                'end_time' => $existingDay?->end_time ?: $settings->work_end_time,
                'notes' => $existingDay ? $existingDay->notes : null,
            ];

            if (is_array($oldDays)) {
                $scheduleDaysData[$i]['is_working_day'] = ! empty($oldDays[$i]['is_working_day']);
                $scheduleDaysData[$i]['start_time'] = $oldDays[$i]['start_time'] ?? $scheduleDaysData[$i]['start_time'];
                $scheduleDaysData[$i]['end_time'] = $oldDays[$i]['end_time'] ?? $scheduleDaysData[$i]['end_time'];
                $scheduleDaysData[$i]['notes'] = $oldDays[$i]['notes'] ?? null;
            }
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
        $maxAllowed = $settings->maxWorkDays();
        $timeFormat = 'regex:/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/';

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'week_start_date' => ['required', 'date'],
            'days' => ['required', 'array', 'size:7'],
            'days.*.is_working_day' => ['nullable', 'boolean'],
            'days.*.start_time' => ['nullable', 'required_if_accepted:days.*.is_working_day', 'string', $timeFormat],
            'days.*.end_time' => ['nullable', 'required_if_accepted:days.*.is_working_day', 'string', $timeFormat],
            'days.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'days.*.start_time.required_if_accepted' => 'Indiquez l\'heure de début de chaque jour travaillé.',
            'days.*.end_time.required_if_accepted' => 'Indiquez l\'heure de fin de chaque jour travaillé.',
            'days.*.start_time.regex' => 'L\'heure de début doit être au format HH:MM.',
            'days.*.end_time.regex' => 'L\'heure de fin doit être au format HH:MM.',
        ]);

        $dayNames = [1 => 'lundi', 2 => 'mardi', 3 => 'mercredi', 4 => 'jeudi', 5 => 'vendredi', 6 => 'samedi', 7 => 'dimanche'];
        foreach ($validated['days'] as $dayOfWeek => $day) {
            if (! empty($day['is_working_day']) && substr($day['end_time'], 0, 5) <= substr($day['start_time'], 0, 5)) {
                return back()->withInput()->withErrors([
                    "days.{$dayOfWeek}.end_time" => 'L\'heure de fin du '.($dayNames[$dayOfWeek] ?? 'jour').' doit être postérieure à l\'heure de début.',
                ]);
            }
        }

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

        // Planning et 7 jours enregistrés ensemble : jamais de planning partiel en cas d'erreur
        $schedule = DB::transaction(function () use ($employee, $weekStart, $workingDaysCount, $validated, $request, $settings): WorkSchedule {
            // Création ou mise à jour du planning hebdomadaire
            $schedule = $this->saveWeekSchedule($employee, $weekStart, [
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

                $this->saveScheduleDay($schedule, $date, [
                    'day_of_week' => $i,
                    'is_working_day' => $isWorking,
                    'start_time' => $isWorking ? ($dayData['start_time'] ?? $settings->work_start_time) : null,
                    'end_time' => $isWorking ? ($dayData['end_time'] ?? $settings->work_end_time) : null,
                    'notes' => $dayData['notes'] ?? null,
                ]
                );
            }

            return $schedule;
        });

        ActivityLogger::log(
            'enregistrement_planning',
            "Planning enregistré pour {$employee->full_name} — Semaine du {$weekStart->format('d/m/Y')} ({$workingDaysCount} jours travaillés)",
            $schedule
        );

        return redirect()->route('rh.schedules.index', ['week' => $weekStart->toDateString()])
            ->with('success', "Planning de {$employee->full_name} validé pour la semaine du {$weekStart->format('d/m/Y')} ({$workingDaysCount} jours ouvrés).");
    }

    /**
     * Application rapide de la semaine standard (jours max et horaires des paramètres RH) à tous les collaborateurs actifs.
     */
    public function applyStandardToAll(Request $request): RedirectResponse
    {
        $settings = HrSetting::current();
        $weekStart = $this->resolveWeekStart($request->input('week'));
        $workingDaysCount = $settings->maxWorkDays();

        $employees = Employee::where('status', 'actif')->get();
        $count = 0;

        DB::transaction(function () use ($employees, $weekStart, $workingDaysCount, $settings, &$count): void {
            foreach ($employees as $employee) {
                $schedule = $this->saveWeekSchedule($employee, $weekStart, [
                    'status' => 'valide',
                    'total_working_days' => $workingDaysCount,
                    'notes' => "Planning standard d'entreprise appliqué en lot ({$workingDaysCount} jours max).",
                    'created_by' => auth()->id(),
                ]
                );

                for ($i = 1; $i <= 7; $i++) {
                    $date = $weekStart->copy()->addDays($i - 1);
                    $isWorking = $settings->isStandardWorkingDay($i);

                    $this->saveScheduleDay($schedule, $date, [
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
        });

        ActivityLogger::log(
            'application_planning_standard_lot',
            "Application du planning standard ({$workingDaysCount} jours max) pour {$count} collaborateurs — Semaine du {$weekStart->format('d/m/Y')}",
            null
        );

        return redirect()->route('rh.schedules.index', ['week' => $weekStart->toDateString()])
            ->with('success', "Planning standard ({$settings->standardWeekLabel()}) appliqué avec succès à {$count} collaborateur(s).");
    }

    /**
     * Crée ou met à jour le planning d'un collaborateur pour une semaine (recherche par date, quel que soit le format stocké).
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function saveWeekSchedule(Employee $employee, Carbon $weekStart, array $attributes): WorkSchedule
    {
        $schedule = WorkSchedule::where('employee_id', $employee->id)
            ->whereDate('week_start_date', $weekStart->toDateString())
            ->first() ?? new WorkSchedule(['employee_id' => $employee->id, 'week_start_date' => $weekStart->toDateString()]);

        $schedule->fill($attributes)->save();

        return $schedule;
    }

    /**
     * Crée ou met à jour la programmation d'un jour du planning.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function saveScheduleDay(WorkSchedule $schedule, Carbon $date, array $attributes): ScheduleDay
    {
        $day = $schedule->days()->whereDate('date', $date->toDateString())->first()
            ?? new ScheduleDay(['work_schedule_id' => $schedule->id, 'date' => $date->toDateString()]);

        $day->fill($attributes)->save();

        return $day;
    }

    /**
     * Lundi de la semaine demandée ; semaine en cours si le paramètre est absent ou invalide.
     */
    protected function resolveWeekStart(mixed $week): Carbon
    {
        if (is_string($week) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $week)) {
            try {
                return Carbon::createFromFormat('!Y-m-d', $week)->startOfWeek();
            } catch (\Throwable) {
                // Date impossible (ex. 2026-02-31) : semaine en cours
            }
        }

        return now()->startOfWeek();
    }

    /**
     * Suppression d'un planning.
     */
    public function destroy(WorkSchedule $schedule): RedirectResponse
    {
        $empName = $schedule->employee?->full_name ?? 'Collaborateur';
        $weekStr = $schedule->week_start_date->format('d/m/Y');
        $schedule->delete();

        ActivityLogger::log('suppression_planning', "Planning de {$empName} supprimé — Semaine du {$weekStr}");

        return back()->with('success', "Planning de {$empName} pour la semaine du {$weekStr} supprimé.");
    }
}
