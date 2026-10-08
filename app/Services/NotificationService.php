<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\SmartAlert;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        protected AutomationEngineService $automationEngine
    ) {}

    /**
     * Récupère les notifications actives destinées à un utilisateur donné.
     */
    public function getNotificationsForUser(User $user, int $limit = 15): Collection
    {
        return $this->buildUserQuery($user)
            ->whereIn('status', ['active', 'read'])
            ->orderByRaw("CASE 
                WHEN status = 'active' THEN 1 
                ELSE 2 END")
            ->orderByRaw("CASE 
                WHEN priority = 'urgente' THEN 1 
                WHEN priority = 'haute' THEN 2 
                WHEN priority = 'moyenne' THEN 3 
                ELSE 4 END")
            ->latest('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Nombre de notifications non lues (actives) pour un utilisateur.
     */
    public function getUnreadCount(User $user): int
    {
        return $this->buildUserQuery($user)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Construit la requête scopée aux accès et domaines de l'utilisateur.
     */
    public function buildUserQuery(User $user): Builder
    {
        $query = SmartAlert::query()->with(['domain', 'assignee']);

        // Si l'utilisateur n'est pas admin et n'a pas accès à tous les domaines,
        // on filtre par les domaines auxquels il est rattaché
        if (! $user->isAdmin() && ! $user->all_domains) {
            $userDomainIds = $user->domains->pluck('id')->toArray();
            if ($user->employee && $user->employee->domain_id) {
                $userDomainIds[] = $user->employee->domain_id;
            }
            $userDomainIds = array_unique(array_filter($userDomainIds));

            $query->where(function ($q) use ($user, $userDomainIds) {
                $q->whereNull('domain_id')
                    ->orWhereIn('domain_id', $userDomainIds)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        return $query;
    }

    /**
     * Marque une notification comme lue.
     */
    public function markAsRead(SmartAlert $alert): bool
    {
        return $alert->update(['status' => 'read']);
    }

    /**
     * Marque toutes les notifications actives de l'utilisateur comme lues.
     */
    public function markAllAsRead(User $user): int
    {
        return $this->buildUserQuery($user)
            ->where('status', 'active')
            ->update(['status' => 'read']);
    }

    /**
     * Marque une notification comme résolue.
     */
    public function resolve(SmartAlert $alert): bool
    {
        return $alert->update(['status' => 'resolved']);
    }

    /**
     * Masque / archive une notification.
     */
    public function dismiss(SmartAlert $alert): bool
    {
        return $alert->update(['status' => 'dismissed']);
    }

    /**
     * Exécute les vérifications et génère les alertes intelligentes.
     */
    public function runChecks(): array
    {
        $baseResult = $this->automationEngine->runChecks();
        $extraGenerated = 0;

        // 1. Demandes de congés en attente de validation
        $pendingLeaves = LeaveRequest::with('employee')
            ->where('status', 'en_attente')
            ->get();

        foreach ($pendingLeaves as $leave) {
            $exists = SmartAlert::where('type', 'leave_request')
                ->where('action_url', route('rh.leaves.index'))
                ->where('status', 'active')
                ->exists();

            if (! $exists) {
                SmartAlert::create([
                    'type' => 'leave_request',
                    'title' => "Demande de congés en attente : {$leave->employee?->full_name}",
                    'description' => "Du {$leave->start_date->format('d/m/Y')} au {$leave->end_date->format('d/m/Y')} ({$leave->days} jour(s)) - {$leave->type}",
                    'priority' => 'moyenne',
                    'domain_id' => $leave->employee?->domain_id,
                    'action_url' => route('rh.leaves.index'),
                    'metadata' => ['leave_id' => $leave->id],
                ]);
                $extraGenerated++;
            }
        }

        // 2. Tickets support ouverts
        $openTickets = SupportTicket::where('status', 'ouvert')->get();
        foreach ($openTickets as $ticket) {
            $exists = SmartAlert::where('type', 'support_ticket')
                ->where('action_url', route('support.show', $ticket->id))
                ->where('status', 'active')
                ->exists();

            if (! $exists) {
                SmartAlert::create([
                    'type' => 'support_ticket',
                    'title' => "Nouveau ticket support : {$ticket->subject}",
                    'description' => "Priorité {$ticket->priority} - Client : {$ticket->customer_name}",
                    'priority' => ($ticket->priority === 'urgent' ? 'urgente' : 'moyenne'),
                    'domain_id' => $ticket->domain_id,
                    'action_url' => route('support.show', $ticket->id),
                    'metadata' => ['ticket_id' => $ticket->id],
                ]);
                $extraGenerated++;
            }
        }

        return [
            'alerts_generated' => $baseResult['alerts_generated'] + $extraGenerated,
            'rules_evaluated' => $baseResult['rules_evaluated'],
        ];
    }
}
