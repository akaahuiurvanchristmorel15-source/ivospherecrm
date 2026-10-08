<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\CommercialAppointment;
use App\Models\Delivery;
use App\Models\Event;
use App\Models\InsuranceAppointment;
use App\Models\LeaveRequest;
use App\Models\PhotoSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = $request->filled('date') ? Carbon::parse($request->date) : Carbon::now();
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        // 1. Commercial Appointments
        $commercialAppointments = CommercialAppointment::with('customer')
            ->whereBetween('scheduled_at', [$startOfMonth->copy()->subDays(7), $endOfMonth->copy()->addDays(7)])
            ->get()
            ->map(function ($app) {
                return [
                    'id' => 'comm_'.$app->id,
                    'title' => 'RdV Commercial : '.($app->customer ? ($app->customer->company_name ?: $app->customer->first_name) : 'Prospect'),
                    'start' => Carbon::parse($app->scheduled_at)->format('Y-m-d H:i'),
                    'date' => Carbon::parse($app->scheduled_at)->format('Y-m-d'),
                    'type' => 'commercial',
                    'color' => '#0066FF',
                    'location' => $app->location ?? 'Bureau / Visio',
                ];
            });

        // 2. Insurance Appointments
        $insuranceAppointments = InsuranceAppointment::with('customer')
            ->whereBetween('scheduled_at', [$startOfMonth->copy()->subDays(7), $endOfMonth->copy()->addDays(7)])
            ->get()
            ->map(function ($app) {
                return [
                    'id' => 'ins_'.$app->id,
                    'title' => 'RdV Assurance : '.($app->customer ? $app->customer->first_name : 'Assuré'),
                    'start' => Carbon::parse($app->scheduled_at)->format('Y-m-d H:i'),
                    'date' => Carbon::parse($app->scheduled_at)->format('Y-m-d'),
                    'type' => 'assurance',
                    'color' => '#0066FF',
                    'location' => $app->location ?? 'Agence',
                ];
            });

        // 3. Photo Sessions
        $photoSessions = PhotoSession::with('customer')
            ->whereBetween('scheduled_at', [$startOfMonth->copy()->subDays(7), $endOfMonth->copy()->addDays(7)])
            ->get()
            ->map(function ($ps) {
                return [
                    'id' => 'photo_'.$ps->id,
                    'title' => 'Session Média / Photo : '.($ps->customer ? $ps->customer->first_name : 'Shooting'),
                    'start' => Carbon::parse($ps->scheduled_at)->format('Y-m-d H:i'),
                    'date' => Carbon::parse($ps->scheduled_at)->format('Y-m-d'),
                    'type' => 'media',
                    'color' => '#0B0F14',
                    'location' => $ps->location ?? 'Studio',
                ];
            });

        // 4. Events
        $events = Event::whereBetween('start_date', [$startOfMonth->copy()->subDays(7), $endOfMonth->copy()->addDays(7)])
            ->get()
            ->map(function ($ev) {
                return [
                    'id' => 'ev_'.$ev->id,
                    'title' => 'Événement : '.$ev->name,
                    'start' => Carbon::parse($ev->start_date)->format('Y-m-d'),
                    'date' => Carbon::parse($ev->start_date)->format('Y-m-d'),
                    'type' => 'event',
                    'color' => '#0066FF',
                    'location' => $ev->location ?? 'Site',
                ];
            });

        // 5. Approved Leaves
        $leaves = LeaveRequest::with('employee')
            ->where('status', 'approuvé')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            })
            ->get()
            ->map(function ($l) {
                return [
                    'id' => 'leave_'.$l->id,
                    'title' => 'Congé : '.($l->employee ? ($l->employee->first_name.' '.$l->employee->last_name) : 'Collaborateur'),
                    'start' => Carbon::parse($l->start_date)->format('Y-m-d'),
                    'date' => Carbon::parse($l->start_date)->format('Y-m-d'),
                    'type' => 'rh',
                    'color' => '#64748B',
                    'location' => 'Absence',
                ];
            });

        // 6. Deliveries
        $deliveries = Delivery::whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$startOfMonth->copy()->subDays(7), $endOfMonth->copy()->addDays(7)])
            ->get()
            ->map(function ($del) {
                return [
                    'id' => 'del_'.$del->id,
                    'title' => 'Livraison : '.$del->tracking_number.' ('.($del->city ?? 'Abidjan').')',
                    'start' => Carbon::parse($del->scheduled_at)->format('Y-m-d H:i'),
                    'date' => Carbon::parse($del->scheduled_at)->format('Y-m-d'),
                    'type' => 'delivery',
                    'color' => '#0066FF',
                    'location' => $del->delivery_address,
                ];
            });

        $allCalendarEvents = $commercialAppointments
            ->concat($insuranceAppointments)
            ->concat($photoSessions)
            ->concat($events)
            ->concat($leaves)
            ->concat($deliveries)
            ->sortBy('start')
            ->values();

        // Group by day for month grid
        $eventsByDate = $allCalendarEvents->groupBy('date');

        return view('calendar.index', compact('selectedDate', 'allCalendarEvents', 'eventsByDate'));
    }
}
