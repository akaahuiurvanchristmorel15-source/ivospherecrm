<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::query()->with(['employee', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('date_start') && $request->filled('date_end')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->date_start, $request->date_end])
                    ->orWhereBetween('end_date', [$request->date_start, $request->date_end]);
            });
        }

        $leaves = $query->latest('created_at')->paginate(15)->withQueryString();
        $employees = Employee::orderBy('first_name')->get();

        return view('rh.leaves.index', compact('leaves', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:congé_annuel,congé_maladie,congé_maternité,sans_solde,autre',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $validated['status'] = 'en_attente';
        $leave = LeaveRequest::create($validated);

        ActivityLogger::log('creation_conge', "Demande de congé créée pour {$leave->employee->full_name}", $leave);

        return redirect()->route('rh.leaves.index')->with('success', 'Demande de congé créée avec succès.');
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $leave->update([
            'status' => 'approuvé',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ActivityLogger::log('approbation_conge', "Demande de congé approuvée pour {$leave->employee->full_name}", $leave);

        return redirect()->back()->with('success', 'Demande de congé approuvée.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $leave->update([
            'status' => 'refusé',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        ActivityLogger::log('refus_conge', "Demande de congé refusée pour {$leave->employee->full_name}", $leave);

        return redirect()->back()->with('success', 'Demande de congé refusée.');
    }
}
