<?php

namespace App\Models;

use App\Services\GeofenceService;
use Database\Factories\AttendanceLocationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Lieu physique associé au QR code de pointage des locaux : l'arrivée et le départ
 * ne sont acceptés que dans le rayon de l'un des lieux actifs.
 */
class AttendanceLocation extends Model
{
    /** @use HasFactory<AttendanceLocationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_meters' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Rayon autorisé en mètres : celui du lieu, ou la valeur par défaut configurée.
     */
    public function effectiveRadius(): int
    {
        return $this->radius_meters ?: (int) config('attendance.geofence.default_radius_meters', 100);
    }

    /**
     * Distance en mètres entre ce lieu et une position GPS.
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        return GeofenceService::distanceMeters((float) $this->latitude, (float) $this->longitude, $latitude, $longitude);
    }
}
