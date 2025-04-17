<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 * name="Messages",
 * description="API endpoints for user-agent messaging"
 * )
 */
class MessageController extends Controller
{
    /**
     * Get the authenticated user's conversations.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $conversations = Conversation::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('user_type', get_class($user));
        })->orWhere(function ($query) use ($user) {
            $query->where('agent_id', $user->id)->where('agent_type', get_class($user));
        })->with(['user', 'agent', 'messages.sender'])->latest('updated_at')->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Get messages for a specific conversation.
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if (!$this->isParticipant($user, $conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->with('sender')->paginate(50);
        return response()->json($messages);
    }

    /**
     * Start a new conversation or send a message to an agent.
     */
    public function store(Request $request, Agent $agent): JsonResponse
    {
        $user = $request->user();
        $validator = Validator::make($request->all(), ['body' => 'required|string|max:500']);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $conversation = Conversation::firstOrCreate([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'agent_id' => $agent->id,
            'agent_type' => get_class($agent),
        ]);

        $message = new Message([
            'body' => $request->body,
        ]);
        $message->sender()->associate($user);
        $conversation->messages()->save($message);

        // Broadcast the new message (if using WebSockets - Step 6)
        Broadcast::to("conversation.{$conversation->id}")->event('newMessage', $message->load('sender'));

        return response()->json($message->load('sender'), 200);
    }

    /**
     * Mark all unread messages in a conversation as read.
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        if (!$this->isParticipant($user, $conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('sender_type', '!=', get_class($user))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    /**
     * Check if the authenticated user is a participant in the conversation.
     */
    protected function isParticipant(mixed $user, Conversation $conversation): bool
    {
        return ($conversation->user_id === $user->id && $conversation->user_type === get_class($user)) ||
               ($conversation->agent_id === $user->id && $conversation->agent_type === get_class($user));
    }
}