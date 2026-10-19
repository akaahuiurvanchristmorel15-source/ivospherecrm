<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Restriction géographique du pointage
    |--------------------------------------------------------------------------
    |
    | Lorsqu'au moins un lieu de pointage actif est défini (RH > Présences >
    | Lieux de pointage), l'arrivée et le départ ne sont acceptés que si la
    | position GPS envoyée par le téléphone se trouve dans le rayon de l'un
    | de ces lieux. Chaque lieu peut surcharger le rayon par défaut.
    |
    */

    'geofence' => [
        'default_radius_meters' => (int) env('ATTENDANCE_GEOFENCE_RADIUS_METERS', 100),
        'max_accuracy_meters' => (int) env('ATTENDANCE_GEOFENCE_MAX_ACCURACY_METERS', 100),
    ],

];
