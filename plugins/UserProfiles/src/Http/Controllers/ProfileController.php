<?php

namespace Plugins\UserProfiles\Http\Controllers;

require_once dirname(__DIR__, 2).'/bootstrap.php';

use App\Http\Controllers\Controller;
use App\Models\ProfileChatReadCursor;
use App\Models\User;
use App\Services\ProfileChatUnreadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Plugins\UserProfiles\Models\ChatMessage;
use Plugins\UserProfiles\Models\E2ePublicKey;
use Plugins\UserProfiles\Models\Friendship;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureTables();

        $query = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where('is_banned', false);

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $users = $query->orderBy('name')->paginate(24)->withQueryString();

        $incoming = Friendship::query()
            ->where('addressee_id', $request->user()->id)
            ->where('status', 'pending')
            ->with(['requester'])
            ->orderByDesc('created_at')
            ->get();

        $conversations = ProfileChatUnreadService::isAvailable()
            ? ProfileChatUnreadService::conversationsFor($request->user())
            : collect();

        return view()->file(
            base_path('plugins/UserProfiles/resources/views/index.blade.php'),
            compact('users', 'incoming', 'search', 'conversations')
        );
    }

    public function inbox(Request $request)
    {
        $this->ensureTables();

        $conversations = ProfileChatUnreadService::isAvailable()
            ? ProfileChatUnreadService::conversationsFor($request->user())
            : collect();

        return view()->file(
            base_path('plugins/UserProfiles/resources/views/inbox.blade.php'),
            [
                'conversations' => $conversations,
                'totalUnread' => ProfileChatUnreadService::totalUnreadCount($request->user()),
            ]
        );
    }

    public function unreadSummary(Request $request): JsonResponse
    {
        $this->ensureTables();

        if (! ProfileChatUnreadService::isAvailable()) {
            return response()->json([
                'total_unread' => 0,
                'conversations' => [],
            ]);
        }

        return response()->json([
            'total_unread' => ProfileChatUnreadService::totalUnreadCount($request->user()),
            'conversations' => ProfileChatUnreadService::unreadSummaryPayload($request->user()),
        ]);
    }

    public function markRead(Request $request, Friendship $friendship): JsonResponse
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if (! $friendship->isAccepted()) {
            abort(403);
        }

        $maxId = (int) $request->input('max_message_id', 0);
        ProfileChatUnreadService::markAsRead($request->user(), $friendship, $maxId > 0 ? $maxId : null);

        return response()->json([
            'ok' => true,
            'total_unread' => ProfileChatUnreadService::totalUnreadCount($request->user()),
        ]);
    }

    public function show(Request $request, User $user)
    {
        $this->ensureTables();

        if ((int) $user->id === (int) $request->user()->id) {
            abort(404);
        }

        if ($user->is_banned) {
            abort(404);
        }

        $friendship = $this->findFriendshipBetween($request->user(), $user);

        return view()->file(
            base_path('plugins/UserProfiles/resources/views/show.blade.php'),
            compact('user', 'friendship')
        );
    }

    public function sendFriendRequest(Request $request, User $user)
    {
        $this->ensureTables();

        if ((int) $user->id === (int) $request->user()->id || $user->is_banned) {
            abort(403);
        }

        $existing = $this->findFriendshipBetween($request->user(), $user);

        if ($existing && $existing->status === 'accepted') {
            return back()->with('success', __('You are already friends.'));
        }

        if ($existing && $existing->status === 'pending') {
            if ((int) $existing->requester_id === (int) $request->user()->id) {
                return back()->with('success', __('Friend request already sent.'));
            }

            return back()->with('success', __('This user has already sent you a request — accept it from your member directory.'));
        }

        if ($existing && $existing->status === 'declined') {
            $existing->delete();
        }

        Friendship::query()->create([
            'requester_id' => $request->user()->id,
            'addressee_id' => $user->id,
            'status' => 'pending',
        ]);

        return back()->with('success', __('Friend request sent.'));
    }

    public function accept(Request $request, Friendship $friendship)
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if ((int) $friendship->addressee_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($friendship->status !== 'pending') {
            return back()->with('success', __('Request is no longer pending.'));
        }

        $friendship->update(['status' => 'accepted']);

        return redirect()
            ->route('profiles.chat', $friendship)
            ->with('success', __('You are now friends. Open the chat to message each other.'));
    }

    public function decline(Request $request, Friendship $friendship)
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if ((int) $friendship->addressee_id !== (int) $request->user()->id) {
            abort(403);
        }

        $friendship->delete();

        return back()->with('success', __('Friend request declined.'));
    }

    public function chat(Request $request, Friendship $friendship)
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if (! $friendship->isAccepted()) {
            abort(403);
        }

        $friendship->load(['requester', 'addressee']);

        $peer = $friendship->peerUser($request->user());
        if (! $peer) {
            abort(403);
        }

        $messages = $friendship->messages()
            ->with('sender')
            ->orderBy('id')
            ->limit(200)
            ->get();

        ProfileChatUnreadService::markAsRead($request->user(), $friendship);

        $myKey = E2ePublicKey::query()->where('user_id', $request->user()->id)->value('public_key_jwk');
        $peerKey = E2ePublicKey::query()->where('user_id', $peer->id)->value('public_key_jwk');

        return view()->file(
            base_path('plugins/UserProfiles/resources/views/chat.blade.php'),
            [
                'friendship' => $friendship,
                'peer' => $peer,
                'messages' => $messages,
                'myPublicKeyJwk' => $myKey,
                'peerPublicKeyJwk' => $peerKey,
                // Direkte URL: funktioniert auch wenn Routen-Cache Plugin-Routen ohne Namen enthält
                'e2eStatusUrl' => url('/friendships/'.$friendship->id.'/e2e-status'),
                'clearMessagesUrl' => url('/friendships/'.$friendship->id.'/messages/clear'),
                'markReadUrl' => route('profiles.messages.mark-read', $friendship),
                'inboxUrl' => route('profiles.inbox'),
            ]
        );
    }

    public function clearMessages(Request $request, Friendship $friendship): JsonResponse
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if (! $friendship->isAccepted()) {
            abort(403);
        }

        ChatMessage::query()->where('friendship_id', $friendship->id)->delete();

        if (ProfileChatUnreadService::isAvailable()) {
            ProfileChatReadCursor::query()
                ->where('friendship_id', $friendship->id)
                ->update(['last_read_message_id' => 0]);
        }

        return response()->json(['ok' => true]);
    }

    public function e2eStatus(Request $request, Friendship $friendship): JsonResponse
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if (! $friendship->isAccepted()) {
            abort(403);
        }

        $peer = $friendship->peerUser($request->user());
        $peerKey = $peer
            ? E2ePublicKey::query()->where('user_id', $peer->id)->value('public_key_jwk')
            : null;
        $myKey = E2ePublicKey::query()->where('user_id', $request->user()->id)->value('public_key_jwk');

        return response()->json([
            'peer_public_key_jwk' => $peerKey,
            'my_public_key_jwk' => $myKey,
        ]);
    }

    public function fetchMessages(Request $request, Friendship $friendship): JsonResponse
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if (! $friendship->isAccepted()) {
            abort(403);
        }

        $afterId = (int) $request->query('after', 0);

        $messages = $friendship->messages()
            ->with('sender')
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(100)
            ->get();

        if ($request->boolean('mark_read')) {
            $latestId = $messages->isNotEmpty()
                ? (int) $messages->max('id')
                : (int) ChatMessage::query()->where('friendship_id', $friendship->id)->max('id');
            ProfileChatUnreadService::markAsRead($request->user(), $friendship, $latestId);
        }

        return response()->json([
            'messages' => $messages->map(static fn (ChatMessage $m) => self::formatMessagePayload($m)),
            'total_unread' => ProfileChatUnreadService::totalUnreadCount($request->user()),
        ]);
    }

    public function sendMessage(Request $request, Friendship $friendship)
    {
        $this->ensureTables();
        $this->authorizeFriendship($request, $friendship);

        if (! $friendship->isAccepted()) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:6291456'],
        ]);

        // FormData liefert Zeichenketten — zuverlässiger als $request->boolean() bei multipart/fetch
        $rawE2e = $request->input('is_e2e');
        $isE2e = $rawE2e === true || $rawE2e === 1 || $rawE2e === '1'
            || $rawE2e === 'true' || $rawE2e === 'on' || $rawE2e === 'yes';

        if ($isE2e) {
            $peer = $friendship->peerUser($request->user());
            $hasSenderKey = E2ePublicKey::query()->where('user_id', $request->user()->id)->exists();
            $hasPeerKey = $peer && E2ePublicKey::query()->where('user_id', $peer->id)->exists();

            if (! $hasSenderKey) {
                throw ValidationException::withMessages([
                    'body' => [__('Open this chat once in your browser so your encryption key is saved — then you can use end-to-end mode.')],
                ]);
            }

            if (! $hasPeerKey) {
                throw ValidationException::withMessages([
                    'body' => [__('To send encrypted messages, the other person must open this chat once in their browser. Until then, use standard chat.')],
                ]);
            }
        }

        $body = $validated['body'];
        if ($isE2e) {
            $decoded = json_decode($body, true);
            if (! is_array($decoded) || ! isset($decoded['iv'], $decoded['ct'])
                || ! is_string($decoded['iv']) || ! is_string($decoded['ct'])) {
                throw ValidationException::withMessages([
                    'body' => [__('Invalid encrypted payload.')],
                ]);
            }
        } else {
            $body = $this->normalizeStandardChatBody($body);
        }

        $message = ChatMessage::query()->create([
            'friendship_id' => $friendship->id,
            'sender_id' => $request->user()->id,
            'body' => $body,
            'is_e2e' => $isE2e,
        ]);

        $message->load('sender');
        ProfileChatUnreadService::markAsRead($request->user(), $friendship, (int) $message->id);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => self::formatMessagePayload($message),
            ]);
        }

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function formatMessagePayload(ChatMessage $m): array
    {
        return [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'body' => $m->body,
            'is_e2e' => $m->is_e2e,
            'created_at' => $m->created_at?->toIso8601String(),
            'sender_name' => $m->sender?->name,
            'preview' => ChatMessage::notificationPreview(
                (string) $m->body,
                (bool) $m->is_e2e,
                auth()->check() && (int) $m->sender_id === (int) auth()->id()
            ),
        ];
    }

    public function storePublicKey(Request $request): JsonResponse
    {
        $this->ensureTables();

        $data = $request->validate([
            'public_key_jwk' => ['required', 'string', 'max:8192'],
        ]);

        $decoded = json_decode($data['public_key_jwk'], true);
        if (! is_array($decoded) || ($decoded['kty'] ?? '') !== 'EC') {
            throw ValidationException::withMessages([
                'public_key_jwk' => [__('Invalid public key format.')],
            ]);
        }

        if (! empty($decoded['d'])) {
            throw ValidationException::withMessages([
                'public_key_jwk' => [__('Only the public key may be uploaded, not the private key.')],
            ]);
        }

        $record = E2ePublicKey::query()->firstOrNew(['user_id' => $request->user()->id]);
        $record->public_key_jwk = $data['public_key_jwk'];
        $record->updated_at = now();
        $record->save();

        return response()->json(['ok' => true]);
    }

    protected function normalizeStandardChatBody(string $body): string
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            throw ValidationException::withMessages([
                'body' => [__('Message cannot be empty.')],
            ]);
        }

        if (! str_starts_with($trimmed, '{"v":1')) {
            return strip_tags($trimmed);
        }

        $decoded = json_decode($trimmed, true);
        if (! is_array($decoded) || ($decoded['v'] ?? null) !== 1) {
            return strip_tags($trimmed);
        }

        $kind = $decoded['k'] ?? '';
        if ($kind === 'text') {
            $decoded['t'] = strip_tags((string) ($decoded['t'] ?? ''));
            if ($decoded['t'] === '') {
                throw ValidationException::withMessages([
                    'body' => [__('Message cannot be empty.')],
                ]);
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if ($kind === 'img') {
            $mime = $decoded['m'] ?? '';
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (! in_array($mime, $allowed, true)) {
                throw ValidationException::withMessages([
                    'body' => [__('Invalid image type.')],
                ]);
            }
            $data = $decoded['d'] ?? '';
            if (! is_string($data) || strlen($data) > 4 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'body' => [__('Image too large.')],
                ]);
            }
            if (isset($decoded['c'])) {
                $decoded['c'] = strip_tags((string) $decoded['c']);
            }

            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        throw ValidationException::withMessages([
            'body' => [__('Invalid message format.')],
        ]);
    }

    protected function findFriendshipBetween(User $a, User $b): ?Friendship
    {
        return Friendship::query()
            ->where(function ($q) use ($a, $b) {
                $q->where('requester_id', $a->id)->where('addressee_id', $b->id);
            })
            ->orWhere(function ($q) use ($a, $b) {
                $q->where('requester_id', $b->id)->where('addressee_id', $a->id);
            })
            ->first();
    }

    protected function authorizeFriendship(Request $request, Friendship $friendship): void
    {
        if (! $friendship->involvesUser($request->user())) {
            abort(403);
        }
    }

    protected function ensureTables(): void
    {
        if (! Schema::hasTable('profile_friendships')) {
            abort(503, __('User profiles plugin database tables are missing. Run migrations.'));
        }
    }
}
