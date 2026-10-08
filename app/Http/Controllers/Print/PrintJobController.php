<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\PrintFinishing;
use App\Models\PrintFormat;
use App\Models\PrintJob;
use App\Models\PrintSupport;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    public function index(Request $request)
    {
        $jobs = PrintJob::with(['customer', 'format'])->latest()->paginate(15);

        return view('print.jobs.index', compact('jobs'));
    }

    public function create()
    {
        $formats = PrintFormat::active()->get();
        $supports = PrintSupport::active()->get();
        $finishings = PrintFinishing::active()->get();
        $customers = Customer::orderBy('name')->get();

        return view('print.jobs.create_edit', compact('formats', 'supports', 'finishings', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:print_jobs,reference',
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|string',
            'format_id' => 'required|exists:print_formats,id',
            'support_id' => 'nullable|exists:print_supports,id',
            'finishing_id' => 'nullable|exists:print_finishings,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|string',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'specifications' => 'nullable|string',
        ]);

        $validated['total'] = $validated['quantity'] * $validated['unit_price'];
        $validated['user_id'] = auth()->id() ?? 1;
        $validated['domain_id'] = Domain::where('code', 'PRINT')->value('id');

        $job = PrintJob::create($validated);
        ActivityLogger::log('create', 'Création du job d\'impression', $job);

        return redirect()->route('print.jobs.index')->with('success', 'Travail d\'impression créé avec succès.');
    }

    public function show(PrintJob $job)
    {
        $job->load(['customer', 'format', 'support', 'finishing']);

        return view('print.jobs.show', compact('job'));
    }

    public function edit(PrintJob $job)
    {
        $formats = PrintFormat::active()->get();
        $supports = PrintSupport::active()->get();
        $finishings = PrintFinishing::active()->get();
        $customers = Customer::orderBy('name')->get();

        return view('print.jobs.create_edit', compact('job', 'formats', 'supports', 'finishings', 'customers'));
    }

    public function update(Request $request, PrintJob $job)
    {
        $validated = $request->validate([
            'reference' => 'required|string|unique:print_jobs,reference,'.$job->id,
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|string',
            'format_id' => 'required|exists:print_formats,id',
            'support_id' => 'nullable|exists:print_supports,id',
            'finishing_id' => 'nullable|exists:print_finishings,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|string',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'specifications' => 'nullable|string',
        ]);

        $validated['total'] = $validated['quantity'] * $validated['unit_price'];

        $job->update($validated);
        ActivityLogger::log('update', 'Mise à jour du job d\'impression', $job);

        return redirect()->route('print.jobs.index')->with('success', 'Travail d\'impression mis à jour avec succès.');
    }

    public function destroy(PrintJob $job)
    {
        ActivityLogger::log('delete', 'Suppression du job d\'impression', $job);
        $job->delete();

        return redirect()->route('print.jobs.index')->with('success', 'Travail d\'impression supprimé avec succès.');
    }
}
