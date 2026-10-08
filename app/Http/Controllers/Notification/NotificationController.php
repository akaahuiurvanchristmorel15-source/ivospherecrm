<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\SmartAlert;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Affiche le Centre de Notifications complet avec filtres et actions.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = $this->notificationService->buildUserQuery($user);

        // Filtre Statut (par défaut : non traitées 'active' & 'read')
        $status = $request->input('status', 'active');
        if ($status === 'active') {
            $query->whereIn('status', ['active', 'read']);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filtre Priorité
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filtre Catégorie / Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtre Domaine
        if ($request->filled('domain_id')) {
            $query->where('domain_id', $request->domain_id);
        }

        // Recherche textuelle
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $notifications = $query->orderByRaw("CASE 
            WHEN status = 'active' THEN 1 
            ELSE 2 END")
            ->orderByRaw("CASE 
            WHEN priority = 'urgente' THEN 1 
            WHEN priority = 'haute' THEN 2 
            WHEN priority = 'moyenne' THEN 3 
            ELSE 4 END")
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $baseCountQuery = $this->notificationService->buildUserQuery($user);
        $counts = [
            'active' => (clone $baseCountQuery)->where('status', 'active')->count(),
            'urgente' => (clone $baseCountQuery)->where('status', 'active')->where('priority', 'urgente')->count(),
            'read' => (clone $baseCountQuery)->where('status', 'read')->count(),
            'resolved' => (clone $baseCountQuery)->where('status', 'resolved')->count(),
        ];

        $domains = Domain::where('is_active', true)->get();

        return view('notifications.index', compact('notifications', 'counts', 'domains'));
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(SmartAlert $notification, Request $request): JsonResponse|RedirectResponse
    {
        $this->notificationService->markAsRead($notification);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $this->notificationService->getUnreadCount(auth()->user()),
            ]);
        }

        return back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications de l'utilisateur comme lues.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $updated = $this->notificationService->markAllAsRead(auth()->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'marked_count' => $updated,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', "Toutes les notifications ({$updated}) ont été marquées comme lues.");
    }

    /**
     * Marquer comme résolue.
     */
    public function resolve(SmartAlert $notification): RedirectResponse
    {
        $this->notificationService->resolve($notification);

        return back()->with('success', "Notification « {$notification->title} » marquée comme résolue.");
    }

    /**
     * Masquer / archiver la notification.
     */
    public function dismiss(SmartAlert $notification): RedirectResponse
    {
        $this->notificationService->dismiss($notification);

        return back()->with('info', 'Notification masquée.');
    }

    /**
     * Déclenche une vérification immédiate des alertes.
     */
    public function runChecks(): RedirectResponse
    {
        $result = $this->notificationService->runChecks();

        return back()->with('success', "Actualisation terminée : {$result['alerts_generated']} alerte(s) détectée(s).");
    }

    /**
     * API JSON du nombre de notifications non lues.
     */
    public function unreadCount(): JsonResponse
    {
        $user = auth()->user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'count' => $count,
            'has_unread' => $count > 0,
        ]);
    }
}
