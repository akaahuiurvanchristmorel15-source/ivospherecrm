<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_start_time',
        'work_end_time',
        'auto_checkout_enabled',
        'auto_checkout_time',
        'geofence_enabled',
        'office_latitude',
        'office_longitude',
        'geofence_radius_meters',
        'dynamic_qr_secret',
        'dynamic_qr_refresh_seconds',
        'max_work_days_per_week',
        'allow_custom_schedules',
        'evaluation_day_of_month',
        'weight_punctuality',
        'weight_tasks',
        'weight_teamwork',
        'max_evaluation_score',
        'punctuality_grace_minutes',
        'late_threshold_minutes',
    ];

    protected function casts(): array
    {
        return [
            'auto_checkout_enabled' => 'boolean',
            'geofence_enabled' => 'boolean',
            'allow_custom_schedules' => 'boolean',
            'office_latitude' => 'decimal:7',
            'office_longitude' => 'decimal:7',
            'geofence_radius_meters' => 'integer',
            'dynamic_qr_refresh_seconds' => 'integer',
            'max_work_days_per_week' => 'integer',
            'evaluation_day_of_month' => 'integer',
            'weight_punctuality' => 'decimal:2',
            'weight_tasks' => 'decimal:2',
            'weight_teamwork' => 'decimal:2',
            'max_evaluation_score' => 'decimal:2',
            'punctuality_grace_minutes' => 'integer',
            'late_threshold_minutes' => 'integer',
        ];
    }

    /**
     * Récupère l'unique instance des paramètres RH ou en crée une avec les valeurs par défaut.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'work_start_time' => '08:00',
            'work_end_time' => '20:00',
            'auto_checkout_enabled' => true,
            'auto_checkout_time' => '20:00',
            'geofence_enabled' => true,
            'office_latitude' => 5.3599520,
            'office_longitude' => -4.0082560,
            'geofence_radius_meters' => 100,
            'dynamic_qr_secret' => bin2hex(random_bytes(16)),
            'dynamic_qr_refresh_seconds' => 60,
            'max_work_days_per_week' => 6,
            'allow_custom_schedules' => true,
            'evaluation_day_of_month' => 5,
            'weight_punctuality' => 0.33,
            'weight_tasks' => 0.23,
            'weight_teamwork' => 0.10,
            'max_evaluation_score' => 30.00,
            'punctuality_grace_minutes' => 5,
            'late_threshold_minutes' => 15,
        ]);
    }

    /**
     * Somme théorique journalière des trois critères (ex: 0.33 + 0.23 + 0.10 = 0.66).
     */
    public function dailyTheoreticalMax(): float
    {
        return (float) $this->weight_punctuality + (float) $this->weight_tasks + (float) $this->weight_teamwork;
    }

    /**
     * Calcule la distance en mètres entre deux coordonnées géographiques (formule Haversine).
     */
    public function calculateDistanceMeters(float $latitude, float $longitude): float
    {
        $earthRadius = 6371000; // Rayon moyen de la Terre en mètres

        $latFrom = deg2rad((float) $this->office_latitude);
        $lonFrom = deg2rad((float) $this->office_longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Détermine si une coordonnée GPS est située dans le rayon autorisé.
     */
    public function isWithinGeofence(float $latitude, float $longitude): bool
    {
        if (! $this->geofence_enabled) {
            return true;
        }

        $distance = $this->calculateDistanceMeters($latitude, $longitude);

        return $distance <= (float) $this->geofence_radius_meters;
    }

    /**
     * Calcule le score de ponctualité selon le retard constaté et les tolérances RH.
     *
     * @return array{score: float, status: string, notes: string}
     */
    public function evaluatePunctuality(int $delayMinutes): array
    {
        $weight = (float) $this->weight_punctuality;
        $grace = $this->punctuality_grace_minutes;
        $lateThreshold = $this->late_threshold_minutes;

        if ($delayMinutes <= $grace) {
            return [
                'score' => $weight,
                'status' => 'present',
                'notes' => $delayMinutes > 0 ? "Arrivée dans la tolérance (+{$delayMinutes} min)" : 'À l\'heure',
            ];
        }

        if ($delayMinutes <= $lateThreshold) {
            // Retard léger : 50% de la note de ponctualité
            $partialScore = round($weight * 0.5, 2);

            return [
                'score' => $partialScore,
                'status' => 'retard',
                'notes' => "Retard léger (+{$delayMinutes} min)",
            ];
        }

        // Retard important au-delà du seuil
        return [
            'score' => 0.00,
            'status' => 'retard',
            'notes' => "Retard important (+{$delayMinutes} min)",
        ];
    }
}
