<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\InternalChannel;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InternalMessageController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->ensureDefaultChannels();

        $channels = InternalChannel::orderBy('name')->get();
        $users = User::where('id', '!=', auth()->id())->get();

        $activeChannelId = $request->get('channel_id');
        $activeRecipientId = $request->get('user_id');

        $activeChannel = null;
        $activeRecipient = null;
        $messages = collect();
        $myId = auth()->id();

        if ($activeRecipientId) {
            $activeRecipient = User::find($activeRecipientId);
            if ($activeRecipient) {
                // Marquer les messages reçus de ce correspondant comme lus
                InternalMessage::where('sender_id', $activeRecipientId)
                    ->where('recipient_id', $myId)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);

                $messages = InternalMessage::where(function ($q) use ($myId, $activeRecipientId) {
                    $q->where('sender_id', $myId)->where('recipient_id', $activeRecipientId);
                })->orWhere(function ($q) use ($myId, $activeRecipientId) {
                    $q->where('sender_id', $activeRecipientId)->where('recipient_id', $myId);
                })->with(['sender', 'recipient'])->oldest()->take(100)->get();
            }
        } else {
            $activeChannel = $activeChannelId ? InternalChannel::find($activeChannelId) : $channels->first();
            if ($activeChannel) {
                $messages = InternalMessage::where('channel_id', $activeChannel->id)
                    ->with('sender')
                    ->oldest()
                    ->take(100)
                    ->get();
            }
        }

        // Compter les messages directs non-lus par expéditeur
        $unreadByUser = InternalMessage::where('recipient_id', $myId)
            ->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id')
            ->toArray();

        // Récupérer le dernier message entrant global pour notifier le destinataire en temps réel
        $latestIncoming = InternalMessage::with(['sender', 'channel'])
            ->where('sender_id', '!=', $myId)
            ->where(function ($q) use ($myId) {
                $q->where('recipient_id', $myId)
                    ->orWhereNull('recipient_id');
            })
            ->latest('id')
            ->first();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'messages' => $messages->map(function ($m) use ($myId) {
                    return [
                        'id' => $m->id,
                        'content' => $m->content,
                        'sender_name' => $m->sender_id === $myId ? 'Vous' : ($m->sender?->name ?? 'Collaborateur'),
                        'is_me' => $m->sender_id === $myId,
                        'created_at' => $m->created_at->format('H:i'),
                    ];
                })->values(),
                'unread_users' => $unreadByUser,
                'latest_incoming' => $latestIncoming ? [
                    'id' => $latestIncoming->id,
                    'sender_id' => $latestIncoming->sender_id,
                    'sender_name' => $latestIncoming->sender?->name ?? 'Collaborateur',
                    'content' => Str::limit($latestIncoming->content, 60),
                    'channel_id' => $latestIncoming->channel_id,
                    'channel_name' => $latestIncoming->channel?->name ?? 'Salon',
                    'is_direct' => ! empty($latestIncoming->recipient_id),
                    'target_url' => ! empty($latestIncoming->recipient_id)
                        ? route('messages.index', ['user_id' => $latestIncoming->sender_id])
                        : route('messages.index', ['channel_id' => $latestIncoming->channel_id]),
                ] : null,
            ]);
        }

        return view('messaging.index', compact('channels', 'users', 'activeChannel', 'activeRecipient', 'messages', 'unreadByUser'));
    }

    public function storeMessage(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'channel_id' => ['nullable', 'exists:internal_channels,id'],
            'recipient_id' => ['nullable', 'exists:users,id'],
        ]);

        $message = InternalMessage::create([
            'channel_id' => $validated['channel_id'] ?? null,
            'recipient_id' => $validated['recipient_id'] ?? null,
            'sender_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_name' => 'Vous',
                    'is_me' => true,
                    'created_at' => $message->created_at->format('H:i'),
                ],
            ]);
        }

        if (! empty($validated['recipient_id'])) {
            return redirect()->route('messages.index', ['user_id' => $validated['recipient_id']])->with('success', 'Message envoyé.');
        }

        return redirect()->route('messages.index', ['channel_id' => $validated['channel_id']])->with('success', 'Message publié.');
    }

    public function storeChannel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $channel = InternalChannel::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('messages.index', ['channel_id' => $channel->id])->with('success', "Salon #{$channel->name} créé.");
    }

    protected function ensureDefaultChannels(): void
    {
        if (InternalChannel::count() === 0) {
            $defaults = [
                ['name' => 'général', 'slug' => 'general', 'description' => 'Discussions générales d\'équipe'],
                ['name' => 'direction', 'slug' => 'direction', 'description' => 'Canal stratégique réservé au management'],
                ['name' => 'commercial', 'slug' => 'commercial', 'description' => 'Objectifs, deals et devis'],
                ['name' => 'print', 'slug' => 'print', 'description' => 'Atelier d\'impression et tirages'],
                ['name' => 'tech', 'slug' => 'tech', 'description' => 'Développements web, mobile et infra'],
                ['name' => 'media', 'slug' => 'media', 'description' => 'Shootings, tournages et locations'],
                ['name' => 'assurance', 'slug' => 'assurance', 'description' => 'Souscriptions et rendez-vous courtiers'],
            ];

            foreach ($defaults as $item) {
                InternalChannel::create(array_merge($item, ['created_by' => auth()->id()]));
            }
        }
    }
}
