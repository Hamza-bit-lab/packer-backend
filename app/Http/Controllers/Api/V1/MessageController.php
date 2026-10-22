<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * One row per person the user has ever exchanged messages with — their
     * most recent message and how many of theirs are still unread — for a
     * WhatsApp-style inbox list.
     */
    public function conversations(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $latestMessageIds = Message::query()
            ->selectRaw('MAX(id) as id')
            ->where(fn ($query) => $query->where('sender_id', $userId)->orWhere('receiver_id', $userId))
            ->groupByRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END', [$userId]);

        $conversations = Message::query()
            ->whereIn('id', $latestMessageIds)
            ->with(['sender', 'receiver'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Message $message) use ($userId) {
                $otherUser = $message->sender_id === $userId ? $message->receiver : $message->sender;

                return (object) [
                    'otherUser' => $otherUser,
                    'lastMessage' => $message,
                    'unreadCount' => Message::where('sender_id', $otherUser->id)
                        ->where('receiver_id', $userId)
                        ->whereNull('read_at')
                        ->count(),
                ];
            });

        return ConversationResource::collection($conversations);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => Message::where('receiver_id', $request->user()->id)->whereNull('read_at')->count(),
        ]);
    }

    /**
     * A direct conversation between the authenticated user and $user — not
     * scoped to any particular booking, so it's reachable from a trip/good's
     * detail screen as well as from an existing booking.
     */
    public function index(Request $request, User $user): AnonymousResourceCollection
    {
        $this->ensureCanMessage($request, $user);

        $messages = Message::query()
            ->where(function ($query) use ($request, $user) {
                $query->where('sender_id', $request->user()->id)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($request, $user) {
                $query->where('sender_id', $user->id)->where('receiver_id', $request->user()->id);
            })
            ->with(['sender', 'receiver'])
            ->oldest()
            ->get();

        $messages->where('receiver_id', $request->user()->id)
            ->whereNull('read_at')
            ->each(fn (Message $message) => $message->update(['read_at' => now()]));

        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request, User $user): MessageResource
    {
        $this->ensureCanMessage($request, $user);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        $user->notify(new NewMessage($message));

        return new MessageResource($message->load(['sender', 'receiver']));
    }

    private function ensureCanMessage(Request $request, User $user): void
    {
        if ($request->user()->id === $user->id) {
            throw ValidationException::withMessages(['user' => 'You cannot message yourself.']);
        }

        if ($request->user()->hasBlockedOrIsBlockedBy($user->id)) {
            throw ValidationException::withMessages(['user' => 'You cannot message this user.']);
        }
    }
}
