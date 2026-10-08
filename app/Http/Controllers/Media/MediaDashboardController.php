<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRental;
use App\Models\Event;
use App\Models\PhotoSession;

class MediaDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'sessions_photo' => PhotoSession::count(),
            'locations_cours' => EquipmentRental::where('status', 'en_cours')->count(),
            'events_planifies' => Event::whereIn('status', ['planification', 'en_cours'])->count(),
        ];

        return view('media.dashboard', compact('stats'));
    }
}
