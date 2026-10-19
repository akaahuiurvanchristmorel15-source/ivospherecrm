<?php

namespace App\Services;

use App\Models\AttendanceLocation;

class GeofenceService
{
    /**
     * Rayon moyen de la Terre en mètres.
     */
    public const EARTH_RADIUS_METERS = 6371000;

    /**
     * Distance en mètres entre deux coordonnées GPS (formule de Haversine).
     */
    public static function distanceMeters(float $fromLatitude, float $fromLongitude, float $toLatitude, float $toLongitude): float
    {
        $latFrom = deg2rad($fromLatitude);
        $latTo = deg2rad($toLatitude);
        $latDelta = $latTo - $latFrom;
        $lonDelta = deg2rad($toLongitude) - deg2rad($fromLongitude);

        $angle = 2 * asin(min(1.0, sqrt(
            sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lonDelta / 2) ** 2
        )));

        return round($angle * self::EARTH_RADIUS_METERS, 2);
    }

    /**
     * Vérifie côté serveur qu'une position GPS se trouve dans le rayon d'un lieu de pointage actif.
     *
     * Sans aucun lieu actif défini, la restriction n'est pas appliquée (enforced = false).
     *
     * @param  array{latitude?: float|int|string|null, longitude?: float|int|string|null, accuracy?: float|int|string|null}  $position
     * @return array{allowed: bool, enforced: bool, error?: string, message?: string, location?: AttendanceLocation, distance_meters?: float, latitude?: float, longitude?: float, accuracy_meters?: float}
     */
    public function check(array $position): array
    {
        $locations = AttendanceLocation::query()->active()->get();

        if ($locations->isEmpty()) {
            return ['allowed' => true, 'enforced' => false];
        }

        $latitude = $position['latitude'] ?? null;
        $longitude = $position['longitude'] ?? null;
        $accuracy = $position['accuracy'] ?? null;

        if (! is_numeric($latitude) || ! is_numeric($longitude) || ! is_numeric($accuracy)) {
            return [
                'allowed' => false,
                'enforced' => true,
                'error' => 'location_missing',
                'message' => 'Pointage refusé : votre position GPS est nécessaire. Autorisez la localisation pour IVOSPHERE sur votre téléphone, activez le GPS puis réessayez.',
            ];
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        $accuracy = (float) $accuracy;
        $maxAccuracy = (int) config('attendance.geofence.max_accuracy_meters', 100);

        if ($accuracy > $maxAccuracy) {
            return [
                'allowed' => false,
                'enforced' => true,
                'error' => 'location_inaccurate',
                'message' => 'Pointage refusé : la précision de votre position GPS (± '.self::formatDistance($accuracy).') est insuffisante (± '.self::formatDistance($maxAccuracy).' maximum). Activez la localisation précise, rapprochez-vous d\'une fenêtre puis réessayez.',
            ];
        }

        $nearest = null;
        $nearestDistance = INF;

        foreach ($locations as $location) {
            $distance = $location->distanceTo($latitude, $longitude);

            if ($distance <= $location->effectiveRadius()) {
                return [
                    'allowed' => true,
                    'enforced' => true,
                    'location' => $location,
                    'distance_meters' => $distance,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy_meters' => $accuracy,
                ];
            }

            if ($distance < $nearestDistance) {
                $nearest = $location;
                $nearestDistance = $distance;
            }
        }

        return [
            'allowed' => false,
            'enforced' => true,
            'error' => 'out_of_zone',
            'message' => 'Pointage refusé : vous êtes hors de la zone autorisée. Le lieu de pointage le plus proche (« '.$nearest->name.' ») est à '.self::formatDistance($nearestDistance).' de vous, le rayon autorisé est de '.self::formatDistance($nearest->effectiveRadius()).'.',
            'location' => $nearest,
            'distance_meters' => $nearestDistance,
        ];
    }

    /**
     * Formate une distance en mètres pour l'affichage (ex. « 85 m », « 1,2 km »).
     */
    public static function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return number_format(round($meters), 0, ',', ' ').' m';
        }

        return number_format($meters / 1000, $meters < 10000 ? 1 : 0, ',', ' ').' km';
    }
}
