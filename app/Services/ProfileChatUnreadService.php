<?php

namespace App\Services;

use App\Models\ProfileChatReadCursor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plugins\UserProfiles\Models\ChatMessage;
use Plugins\UserProfiles\Models\Friendship;

class ProfileChatUnreadService
{
    public static function isAvailable(): bool
    {
        return Schema::hasTable('profile_chat_read_cursors')
            && Schema::hasTable('profile_chat_messages');
    }

    public static function markAsRead(User $user, Friendship $friendship, ?int $maxMessageId = null): void
    {
        if (! self::isAvailable()) {
            return;
        }

        if ($maxMessageId === null) {
            $maxMessageId = (int) ChatMessage::query()
                ->where('friendship_id', $friendship->id)
                ->max('id');
        }

        ProfileChatReadCursor::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'friendship_id' => $friendship->id,
            ],
            ['last_read_message_id' => max(0, $maxMessageId)]
        );
    }

    public static function unreadCountForFriendship(User $user, int $friendshipId): int
    {
        if (! self::isAvailable()) {
            return 0;
        }

        $lastRead = (int) ProfileChatReadCursor::query()
            ->where('user_id', $user->id)
            ->where('friendship_id', $friendshipId)
            ->value('last_read_message_id');

        return ChatMessage::query()
            ->where('friendship_id', $friendshipId)
            ->where('sender_id', '!=', $user->id)
            ->where('id', '>', $lastRead)
            ->count();
    }

    public static function totalUnreadCount(User $user): int
    {
        if (! self::isAvailable()) {
            return 0;
        }

        $friendshipIds = self::acceptedFriendshipIdsFor($user);
        if ($friendshipIds === []) {
            return 0;
        }

        $cursors = ProfileChatReadCursor::query()
            ->where('user_id', $user->id)
            ->whereIn('friendship_id', $friendshipIds)
            ->pluck('last_read_message_id', 'friendship_id');

        $total = 0;
        foreach ($friendshipIds as $friendshipId) {
            $lastRead = (int) ($cursors[$friendshipId] ?? 0);
            $total += ChatMessage::query()
                ->where('friendship_id', $friendshipId)
                ->where('sender_id', '!=', $user->id)
                ->where('id', '>', $lastRead)
                ->count();
        }

        return $total;
    }

    /**
     * @return list<int>
     */
    public static function acceptedFriendshipIdsFor(User $user): array
    {
        return Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('addressee_id', $user->id);
            })
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, array{
     *   friendship: Friendship,
     *   peer: User,
     *   unread_count: int,
     *   last_message: ?ChatMessage,
     *   preview: string,
     *   chat_url: string
     * }>
     */
    public static function conversationsFor(User $user): Collection
    {
        $friendships = Friendship::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('addressee_id', $user->id);
            })
            ->with(['requester', 'addressee'])
            ->orderByDesc('updated_at')
            ->get();

        if ($friendships->isEmpty()) {
            return collect();
        }

        $ids = $friendships->pluck('id')->all();

        $lastMessageIds = ChatMessage::query()
            ->select('friendship_id', DB::raw('MAX(id) as max_id'))
            ->whereIn('friendship_id', $ids)
            ->groupBy('friendship_id')
            ->pluck('max_id', 'friendship_id');

        $messages = ChatMessage::query()
            ->with('sender')
            ->whereIn('id', $lastMessageIds->filter()->values())
            ->get()
            ->keyBy('friendship_id');

        return $friendships->map(function (Friendship $friendship) use ($user, $messages) {
            $peer = $friendship->peerUser($user);
            if (! $peer) {
                return null;
            }

            $lastMessage = $messages->get($friendship->id);
            $unread = self::unreadCountForFriendship($user, (int) $friendship->id);

            return [
                'friendship' => $friendship,
                'peer' => $peer,
                'unread_count' => $unread,
                'last_message' => $lastMessage,
                'preview' => $lastMessage
                    ? ChatMessage::notificationPreview($lastMessage->body, $lastMessage->is_e2e, (int) $lastMessage->sender_id === (int) $user->id)
                    : __('No messages yet'),
                'chat_url' => route('profiles.chat', $friendship),
            ];
        })
            ->filter()
            ->sortByDesc(function (array $row) {
                $msg = $row['last_message'];

                return $msg?->created_at?->timestamp ?? $row['friendship']->updated_at?->timestamp ?? 0;
            })
            ->values();
    }

    /**
     * @return list<array{
     *   friendship_id: int,
     *   peer_name: string,
     *   unread_count: int,
     *   preview: string,
     *   chat_url: string
     * }>
     */
    public static function unreadSummaryPayload(User $user): array
    {
        return self::conversationsFor($user)
            ->filter(static fn (array $row) => $row['unread_count'] > 0)
            ->map(static function (array $row) {
                return [
                    'friendship_id' => (int) $row['friendship']->id,
                    'peer_name' => (string) $row['peer']->name,
                    'unread_count' => (int) $row['unread_count'],
                    'preview' => (string) $row['preview'],
                    'chat_url' => (string) $row['chat_url'],
                ];
            })
            ->values()
            ->all();
    }
}
