<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Services\ActivityLogger;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Liste et registre des présences et pointages.
     */
    public function index(Request $request): View
    {
        $query = Attendance::query()->with('employee');

        $date = $request->input('date', now()->toDateString());
        $query->where('date', $date);

        if ($request->filled('department')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('check_out_type')) {
            $query->where('check_out_type', $request->check_out_type);
        }

        $attendances = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $employees = Employee::where('status', 'actif')->orderBy('first_name')->get();
        $departments = Employee::select('department')->whereNotNull('department')->distinct()->pluck('department');
        $settings = HrSetting::current();

        // Statistiques du jour
        $presentCount = Attendance::whereDate('date', $date)->whereIn('status', ['present', 'retard'])->count();
        $lateCount = Attendance::whereDate('date', $date)->where('status', 'retard')->count();
        $autoCheckoutCount = Attendance::whereDate('date', $date)->where('check_out_type', 'automatic')->count();
        $totalActiveEmployees = Employee::where('status', 'actif')->count();
        $absentCount = max(0, $totalActiveEmployees - $presentCount);

        return view('rh.attendance.index', compact(
            'attendances',
            'employees',
            'date',
            'departments',
            'settings',
            'presentCount',
            'lateCount',
            'autoCheckoutCount',
            'absentCount',
            'totalActiveEmployees'
        ));
    }

    /**
     * Borne d'affichage du QR Code dynamique au siège de l'entreprise (accueil / locaux).
     */
    public function terminal(): View
    {
        $settings = HrSetting::current();
        $payload = $this->attendanceService->generateDynamicQrPayload();

        return view('rh.attendance.terminal', compact('settings', 'payload'));
    }

    /**
     * Affiche officielle A4 imprimable du QR Code de pointage des locaux,
     * renouvelé et réactualisé automatiquement chaque 1 mois.
     */
    public function poster(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $settings = HrSetting::current();
        $payload = $this->attendanceService->generateMonthlyQrPayload($year, $month);

        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        $availableMonths = [];
        for ($i = -1; $i <= 2; $i++) {
            $dt = now()->addMonths($i);
            $availableMonths[] = [
                'year' => $dt->year,
                'month' => $dt->month,
                'label' => ($monthNames[$dt->month] ?? '').' '.$dt->year,
                'is_current' => ($dt->year === now()->year && $dt->month === now()->month),
            ];
        }

        return view('rh.attendance.poster', compact('settings', 'payload', 'year', 'month', 'availableMonths'));
    }

    /**
     * API JSON renvoyant le jeton du QR Code dynamique rafraîchi pour la borne d'accueil.
     */
    public function dynamicQrToken(): JsonResponse
    {
        $payload = $this->attendanceService->generateDynamicQrPayload();

        return response()->json($payload);
    }

    /**
     * Interface mobile de pointage (Caméra + GPS + Validation immédiate).
     */
    public function scanner(): View
    {
        $user = auth()->user();
        $employee = $user?->employee ?: Employee::where('user_id', $user?->id)->orWhere('email', $user?->email)->first();
        $settings = HrSetting::current();
        $todayAttendance = $employee ? Attendance::where('employee_id', $employee->id)->whereDate('date', today())->first() : null;

        return view('rh.attendance.scanner', compact('employee', 'settings', 'todayAttendance'));
    }

    /**
     * Traitement de la requête de pointage d'arrivée.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_payload' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
            'device_fingerprint' => ['nullable', 'string'],
            'device_info' => ['nullable', 'string'],
        ]);

        $result = $this->attendanceService->checkIn(auth()->user(), $validated);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Traitement de la requête de pointage de départ manuel.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $result = $this->attendanceService->checkOut(auth()->user(), $request->all());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Enregistrement ou rectification manuelle d'une présence par le RH.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
            'check_out_type' => 'required|in:manual,automatic',
            'status' => 'required|in:present,absent,retard,congé',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        ActivityLogger::log('enregistrement_presence', "Présence enregistrée manuellement pour {$attendance->employee->full_name}", $attendance);

        return redirect()->back()->with('success', 'Présence enregistrée avec succès.');
    }
}
