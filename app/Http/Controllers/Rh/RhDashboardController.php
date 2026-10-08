<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\DailyTaskSheet;
use App\Models\Employee;
use App\Models\EmployeeContract;
use App\Models\LeaveRequest;
use App\Models\MonthlyEvaluation;
use App\Models\SalesTarget;

class RhDashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $totalEmployees = Employee::where('status', 'actif')->count();
        $onLeave = LeaveRequest::where('status', 'approuvé')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        $presentToday = Attendance::where('date', $today)
            ->whereIn('status', ['present', 'retard'])
            ->count();

        $lateToday = Attendance::where('date', $today)
            ->where('is_late', true)
            ->count();

        $activeContracts = EmployeeContract::active()->count();

        $activeSalesTargetsCount = SalesTarget::where('status', 'en_cours')->count();
        $totalSalesTargetAmount = (float) SalesTarget::where('status', 'en_cours')->sum('target_amount');
        $totalAchievedSalesAmount = (float) SalesTarget::where('status', 'en_cours')->sum('achieved_amount');

        // Note moyenne de la campagne du mois en cours (/30)
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $monthlyEvaluationAvg = round((float) MonthlyEvaluation::where('year', $currentYear)
            ->where('month', $currentMonth)
            ->avg('final_score_30'), 1);

        // Tâches du jour en attente de validation par les managers
        $pendingTaskValidations = DailyTaskSheet::whereDate('date', $today)
            ->get()
            ->sum(function ($sheet) {
                return collect($sheet->tasks ?? [])->where('validation_status', 'en_attente')->count();
            });

        // Derniers pointages du jour
        $latestAttendances = Attendance::with('employee')
            ->where('date', $today)
            ->orderByRaw('COALESCE(check_out, check_in) DESC')
            ->take(5)
            ->get();

        $recentActivities = ActivityLog::where('module', 'rh')
            ->orWhereIn('action', [
                'creation_employe', 'modification_employe', 'approbation_conge',
                'pointage_arrivee', 'pointage_depart', 'pointage_depart_automatique',
                'validation_tache_manager', 'validation_evaluation_mensuelle',
            ])
            ->latest()
            ->take(6)
            ->get();

        return view('rh.index', compact(
            'totalEmployees',
            'onLeave',
            'presentToday',
            'lateToday',
            'activeContracts',
            'activeSalesTargetsCount',
            'totalSalesTargetAmount',
            'totalAchievedSalesAmount',
            'monthlyEvaluationAvg',
            'pendingTaskValidations',
            'latestAttendances',
            'recentActivities'
        ));
    }
}
