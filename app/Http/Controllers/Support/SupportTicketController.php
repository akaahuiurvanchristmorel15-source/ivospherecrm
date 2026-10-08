<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::query()->with(['customer', 'user', 'domain']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        $tickets = $query->orderByRaw("CASE 
            WHEN priority = 'urgente' THEN 1 
            WHEN priority = 'haute' THEN 2 
            WHEN priority = 'normale' THEN 3 
            ELSE 4 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $domains = Domain::all();
        $counts = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::whereIn('status', ['nouveau', 'en_cours', 'attente_client'])->count(),
            'urgent' => SupportTicket::whereIn('status', ['nouveau', 'en_cours'])->where('priority', 'urgente')->count(),
            'resolved' => SupportTicket::whereIn('status', ['resolu', 'ferme'])->count(),
        ];

        return view('support.index', compact('tickets', 'domains', 'counts'));
    }

    public function create(): View
    {
        $customers = Customer::active()->get();
        $domains = Domain::all();
        $users = User::all();

        return view('support.create', compact('customers', 'domains', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string'],
            'priority' => ['required', 'string'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'domain_id' => ['nullable', 'exists:domains,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $ticketCount = SupportTicket::count() + 1;
        $ticketNumber = 'TCK-'.date('Y').'-'.str_pad((string) $ticketCount, 4, '0', STR_PAD_LEFT);

        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'nouveau',
            'customer_id' => $validated['customer_id'] ?? null,
            'domain_id' => $validated['domain_id'] ?? null,
            'user_id' => $validated['user_id'] ?? auth()->id(),
        ]);

        // Add first message
        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'customer_id' => $validated['customer_id'] ?? null,
            'message' => $validated['description'],
            'is_internal_note' => false,
        ]);

        return redirect()->route('support.show', $ticket)->with('success', "Ticket {$ticket->ticket_number} créé avec succès.");
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['customer', 'user', 'domain', 'messages.user', 'messages.customer']);

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal_note' => $request->boolean('is_internal_note'),
        ]);

        if ($ticket->status === 'nouveau') {
            $ticket->update(['status' => 'en_cours']);
        }

        return redirect()->route('support.show', $ticket)->with('success', 'Message ajouté au fil du ticket.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:nouveau,en_cours,attente_client,resolu,ferme'],
        ]);

        $ticket->status = $validated['status'];
        if (in_array($validated['status'], ['resolu', 'ferme'])) {
            $ticket->closed_at = Carbon::now();
        } else {
            $ticket->closed_at = null;
        }
        $ticket->save();

        return back()->with('success', "Statut du ticket mis à jour : {$validated['status']}.");
    }
}
