<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DailyEvaluationScore;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\HrSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class AttendanceService
{
    /**
     * Génère la charge utile signée et horodatée du QR code dynamique des locaux.
     *
     * @return array{token: string, generated_at: int, expires_in: int, office_name: string, latitude: float, longitude: float}
     */
    public function generateDynamicQrPayload(): array
    {
        $settings = HrSetting::current();
        $secret = $settings->dynamic_qr_secret ?: 'ivosphere_qr_secret_key';
        $timestamp = time();
        $refreshSec = max(15, (int) $settings->dynamic_qr_refresh_seconds);
        $window = (int) floor($timestamp / $refreshSec);

        $signature = hash_hmac('sha256', "ivo_premises_{$window}", $secret);
        $token = "IVO-LOC-{$window}-".substr($signature, 0, 16);

        return [
            'token' => $token,
            'generated_at' => $timestamp,
            'expires_in' => $refreshSec - ($timestamp % $refreshSec),
            'office_name' => 'Siège IVOSPHERE ERP',
            'latitude' => (float) $settings->office_latitude,
            'longitude' => (float) $settings->office_longitude,
            'radius' => (int) $settings->geofence_radius_meters,
        ];
    }

    /**
     * Valide un jeton de QR code dynamique affiché dans les locaux (avec tolérance d'une fenêtre).
     */
    public function verifyDynamicQrToken(string $token): bool
    {
        $settings = HrSetting::current();
        $secret = $settings->dynamic_qr_secret ?: 'ivosphere_qr_secret_key';
        $refreshSec = max(15, (int) $settings->dynamic_qr_refresh_seconds);
        $currentWindow = (int) floor(time() / $refreshSec);

        // Fenêtre courante ou fenêtre immédiatement précédente (tolérance de rafraîchissement)
        foreach ([$currentWindow, $currentWindow - 1] as $w) {
            $expectedSignature = hash_hmac('sha256', "ivo_premises_{$w}", $secret);
            $expectedToken = "IVO-LOC-{$w}-".substr($expectedSignature, 0, 16);
            if (hash_equals($expectedToken, trim($token))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Génère la charge utile du QR Code officiel imprimable des locaux,
     * renouvelé et réactualisé automatiquement chaque mois (valable 1 mois).
     *
     * @return array{token: string, year: int, month: int, month_name: string, valid_from: string, valid_until: string, days_remaining: int, is_current: bool, office_name: string, latitude: float, longitude: float, radius: int}
     */
    public function generateMonthlyQrPayload(?int $year = null, ?int $month = null): array
    {
        $settings = HrSetting::current();
        $secret = $settings->dynamic_qr_secret ?: 'ivosphere_qr_secret_key';

        $year = $year ?: (int) now()->year;
        $month = $month ?: (int) now()->month;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

        $monthKey = sprintf('%04d-%02d', $year, $month);
        $signature = hash_hmac('sha256', "ivo_premises_monthly_{$monthKey}", $secret);
        $token = "IVO-MONTH-{$monthKey}-".substr($signature, 0, 16);

        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return [
            'token' => $token,
            'year' => $year,
            'month' => $month,
            'month_name' => ($monthNames[$month] ?? 'Mois').' '.$year,
            'valid_from' => $startDate->format('d/m/Y'),
            'valid_until' => $endDate->format('d/m/Y à 23:59'),
            'days_remaining' => max(0, (int) now()->diffInDays($endDate, false)),
            'is_current' => (now()->year === $year && now()->month === $month),
            'office_name' => 'Siège Social IVOSPHERE',
            'latitude' => (float) $settings->office_latitude,
            'longitude' => (float) $settings->office_longitude,
            'radius' => (int) $settings->geofence_radius_meters,
        ];
    }

    /**
     * Valide le jeton d'un QR code mensuel imprimé dans les locaux.
     * Le code est renouvelé automatiquement chaque 1 mois et expire au changement de mois.
     */
    public function verifyMonthlyQrToken(string $token): bool
    {
        $settings = HrSetting::current();
        $secret = $settings->dynamic_qr_secret ?: 'ivosphere_qr_secret_key';

        // 1. Mois en cours
        $currentMonthKey = now()->format('Y-m');
        $currentSig = hash_hmac('sha256', "ivo_premises_monthly_{$currentMonthKey}", $secret);
        $expectedCurrentToken = "IVO-MONTH-{$currentMonthKey}-".substr($currentSig, 0, 16);

        if (hash_equals($expectedCurrentToken, trim($token))) {
            return true;
        }

        // 2. Tolérance de transition : les 2 premiers jours du mois, l'affiche du mois précédent reste admise
        // afin de laisser le temps au service RH d'installer la nouvelle affiche imprimée.
        if (now()->day <= 2) {
            $prevMonthKey = now()->subMonth()->format('Y-m');
            $prevSig = hash_hmac('sha256', "ivo_premises_monthly_{$prevMonthKey}", $secret);
            $expectedPrevToken = "IVO-MONTH-{$prevMonthKey}-".substr($prevSig, 0, 16);
            if (hash_equals($expectedPrevToken, trim($token))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enregistre un pointage d'arrivée avec vérification simultanée :
     * QR code + Utilisateur + GPS + Heure + Planning.
     *
     * @param  array{qr_payload: string, latitude: float|null, longitude: float|null, accuracy: float|null, device_fingerprint: string|null, device_info: string|null}  $data
     * @return array{success: bool, message: string, error?: string, attendance?: Attendance, delay_minutes?: int, punctuality_score?: float, distance?: float}
     */
    public function checkIn(User $user, array $data): array
    {
        // 1. Identification de l'employé
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();

        if (! $employee || $employee->status !== 'actif') {
            return [
                'success' => false,
                'error' => 'employee_not_found',
                'message' => 'Aucun dossier employé actif associé à votre compte utilisateur.',
            ];
        }

        $settings = HrSetting::current();
        $qrPayload = trim($data['qr_payload'] ?? '');

        // 2. Vérification du QR code (affiche mensuelle imprimable, écran dynamique ou badge personnel)
        $qrType = 'premises_dynamic';
        if (str_starts_with($qrPayload, 'IVO-MONTH-')) {
            if (! $this->verifyMonthlyQrToken($qrPayload)) {
                return [
                    'success' => false,
                    'error' => 'expired_monthly_qr',
                    'message' => 'L\'affiche QR Code de pointage est expirée (valable pour un autre mois). Veuillez scanner l\'affiche officielle du mois en cours.',
                ];
            }
            $qrType = 'premises_monthly_poster';
        } elseif (str_starts_with($qrPayload, 'IVO-LOC-')) {
            if (! $this->verifyDynamicQrToken($qrPayload)) {
                return [
                    'success' => false,
                    'error' => 'invalid_qr_token',
                    'message' => 'Le QR code dynamique scanné est expiré ou invalide. Veuillez rescanner le code affiché.',
                ];
            }
        } elseif ($qrPayload === $employee->employee_code || $qrPayload === "IVO-EMP:{$employee->employee_code}") {
            $qrType = 'personal_badge';
        } else {
            return [
                'success' => false,
                'error' => 'qr_mismatch',
                'message' => 'QR Code non reconnu. Scannez l\'affiche officielle de pointage du mois ou votre badge personnel.',
            ];
        }

        // 3. Restriction géographique (Géofencing)
        $lat = isset($data['latitude']) ? (float) $data['latitude'] : null;
        $lng = isset($data['longitude']) ? (float) $data['longitude'] : null;
        $accuracy = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        $distance = null;

        if ($settings->geofence_enabled) {
            if ($lat === null || $lng === null) {
                return [
                    'success' => false,
                    'error' => 'gps_required',
                    'message' => 'La localisation GPS est requise pour valider le pointage. Veuillez autoriser la géolocalisation sur votre smartphone.',
                ];
            }

            $distance = $settings->calculateDistanceMeters($lat, $lng);
            if ($distance > (float) $settings->geofence_radius_meters) {
                return [
                    'success' => false,
                    'error' => 'outside_geofence',
                    'message' => "Pointage refusé. Vous êtes en dehors de la zone de pointage autorisée (distance constatée : {$distance} m, rayon autorisé : {$settings->geofence_radius_meters} m).",
                    'distance' => $distance,
                ];
            }
        }

        $today = today();

        // 4. Vérification du planning de l'employé
        $schedule = $employee->currentWeekSchedule();
        $scheduleDay = $schedule ? $schedule->getDayForDate($today) : null;

        if ($scheduleDay && ! $scheduleDay->is_working_day) {
            return [
                'success' => false,
                'error' => 'day_off',
                'message' => 'Pointage refusé : aujourd\'hui est enregistré comme votre jour de repos hebdomadaire sur votre planning.',
            ];
        }

        $scheduledStartTime = $scheduleDay?->start_time ?: $settings->work_start_time ?: '08:00';

        // 5. Vérification si déjà pointé aujourd'hui
        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('date', $today)->first();
        if ($attendance && $attendance->isCheckedIn()) {
            $timeDisplay = $attendance->check_in_at ? $attendance->check_in_at->format('H:i') : substr($attendance->check_in, 0, 5);

            return [
                'success' => false,
                'error' => 'already_checked_in',
                'message' => "Arrivée déjà enregistrée aujourd'hui à {$timeDisplay}.",
                'attendance' => $attendance,
            ];
        }

        // 6. Calcul de ponctualité
        $now = now();
        $scheduledStartCarbon = Carbon::parse($today->toDateString().' '.$scheduledStartTime);
        $delayMinutes = 0;
        if ($now->greaterThan($scheduledStartCarbon)) {
            $delayMinutes = (int) round($scheduledStartCarbon->diffInMinutes($now));
        }

        $punctuality = $settings->evaluatePunctuality($delayMinutes);

        // 7. Enregistrement de la présence
        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'check_in' => $now->format('H:i:s'),
                'check_in_at' => $now,
                'latitude' => $lat,
                'longitude' => $lng,
                'accuracy_meters' => $accuracy,
                'distance_meters' => $distance,
                'location_verified' => true,
                'qr_type' => $qrType,
                'device_fingerprint' => $data['device_fingerprint'] ?? null,
                'device_info' => $data['device_info'] ?? Request::userAgent(),
                'ip_address' => Request::ip(),
                'delay_minutes' => $delayMinutes,
                'punctuality_score' => $punctuality['score'],
                'status' => $punctuality['status'],
                'notes' => $punctuality['notes'],
            ]
        );

        // 8. Enregistrement du score journalier pour l'évaluation mensuelle
        DailyEvaluationScore::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'is_working_day' => true,
                'punctuality_score' => $punctuality['score'],
                'punctuality_notes' => $punctuality['notes'],
            ]
        );

        // 9. Génération automatique de la fiche de tâches du jour si non existante
        $this->ensureDailyTaskSheet($employee, $today);

        // 10. Journalisation
        ActivityLogger::log(
            'pointage_arrivee_qr',
            "Arrivée validée pour {$employee->full_name} ({$punctuality['notes']}, score ponctualité : {$punctuality['score']})",
            $attendance
        );

        return [
            'success' => true,
            'message' => "Pointage d'arrivée validé avec succès à {$now->format('H:i')} !",
            'attendance' => $attendance,
            'delay_minutes' => $delayMinutes,
            'punctuality_score' => (float) $punctuality['score'],
            'distance' => $distance,
            'status' => $punctuality['status'],
        ];
    }

    /**
     * Enregistre un pointage de départ manuel par l'employé.
     */
    public function checkOut(User $user, array $data = []): array
    {
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->orWhere('email', $user->email)->first();

        if (! $employee) {
            return [
                'success' => false,
                'error' => 'employee_not_found',
                'message' => 'Aucun dossier employé actif associé à ce compte.',
            ];
        }

        $today = today();
        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('date', $today)->first();

        if (! $attendance || ! $attendance->isCheckedIn()) {
            return [
                'success' => false,
                'error' => 'not_checked_in',
                'message' => 'Impossible d\'enregistrer le départ : vous n\'avez pas pointé votre arrivée aujourd\'hui.',
            ];
        }

        if ($attendance->isCheckedOut()) {
            $timeDisplay = $attendance->check_out_at ? $attendance->check_out_at->format('H:i') : substr($attendance->check_out, 0, 5);

            return [
                'success' => false,
                'error' => 'already_checked_out',
                'message' => "Votre départ a déjà été validé à {$timeDisplay} (Mode : {$attendance->check_out_type}).",
                'attendance' => $attendance,
            ];
        }

        $now = now();
        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'check_out_at' => $now,
            'check_out_type' => 'manual',
            'notes' => ($attendance->notes ? $attendance->notes.' • ' : '')."Départ scanné à {$now->format('H:i')}.",
        ]);

        ActivityLogger::log(
            'pointage_depart_manuel',
            "Départ manuel validé pour {$employee->full_name} à {$now->format('H:i')}",
            $attendance
        );

        return [
            'success' => true,
            'message' => "Pointage de départ enregistré avec succès à {$now->format('H:i')} (Mode manuel).",
            'attendance' => $attendance,
        ];
    }

    /**
     * Clôture automatique de tous les départs non scannés pour la journée en cours.
     */
    public function autoCheckoutPending(): int
    {
        $settings = HrSetting::current();
        if (! $settings->auto_checkout_enabled) {
            return 0;
        }

        $autoTime = $settings->auto_checkout_time ?: '20:00';
        $today = today();

        $pendingAttendances = Attendance::whereDate('date', $today)
            ->whereNotNull('check_in')
            ->whereNull('check_out')
            ->get();

        $count = 0;
        foreach ($pendingAttendances as $attendance) {
            $attendance->update([
                'check_out' => "{$autoTime}:00",
                'check_out_type' => 'automatic',
                'notes' => trim(($attendance->notes ? $attendance->notes.' • ' : '')."Départ automatique clôturé à {$autoTime} (aucun scan de sortie enregistré)."),
            ]);
            $count++;
        }

        if ($count > 0) {
            ActivityLogger::log(
                'cloture_automatique_presences',
                "Clôture automatique de la journée à {$autoTime} pour {$count} collaborateur(s)",
                null
            );
        }

        return $count;
    }

    /**
     * S'assure qu'une fiche de tâches quotidienne existe lors de l'arrivée de l'employé.
     */
    protected function ensureDailyTaskSheet(Employee $employee, Carbon $date): void
    {
        $existing = DailyTaskSheet::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        if (! $existing) {
            DailyTaskSheet::create([
                'reference' => DailyTaskSheet::generateReference(),
                'employee_id' => $employee->id,
                'date' => $date,
                'position' => $employee->position ?? 'Collaborateur',
                'tasks' => [
                    ['id' => 1, 'title' => 'Préparer les dossiers et priorités de la journée', 'status' => 'a_faire', 'validation_status' => 'en_attente'],
                    ['id' => 2, 'title' => 'Assurer le suivi des clients et commandes assignées', 'status' => 'a_faire', 'validation_status' => 'en_attente'],
                    ['id' => 3, 'title' => 'Mise à jour des saisies sur IVOSPHERE ERP', 'status' => 'a_faire', 'validation_status' => 'en_attente'],
                ],
                'notes' => 'Fiche générée automatiquement lors du pointage d\'arrivée.',
                'status' => 'assigne',
            ]);
        }
    }
}
