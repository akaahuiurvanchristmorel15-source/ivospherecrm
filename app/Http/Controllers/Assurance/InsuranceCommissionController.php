<?php

namespace App\Http\Controllers\Assurance;

use App\Http\Controllers\Controller;
use App\Models\InsuranceCommission;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class InsuranceCommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = InsuranceCommission::with(['contract'])->latest()->paginate(15);

        return view('assurance.commissions.index', compact('commissions'));
    }

    public function markPaid(Request $request, InsuranceCommission $commission)
    {
        $commission->update(['status' => 'payée', 'paid_at' => now()]);
        ActivityLogger::log('update', 'Paiement de commission', $commission);

        return redirect()->route('assurance.commissions.index')->with('success', 'Commission marquée comme payée.');
    }
}
