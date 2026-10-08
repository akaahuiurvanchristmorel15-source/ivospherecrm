<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\PrintFinishing;
use App\Models\PrintFormat;
use App\Models\PrintSupport;
use Illuminate\View\View;

class SimulatorController extends Controller
{
    public function index(): View
    {
        $formats = PrintFormat::all();
        $supports = PrintSupport::all();
        $finishings = PrintFinishing::all();
        $equipments = Equipment::where('status', 'disponible')->get();
        $customers = Customer::where('is_active', true)->take(15)->get();

        return view('simulators.index', compact('formats', 'supports', 'finishings', 'equipments', 'customers'));
    }
}
