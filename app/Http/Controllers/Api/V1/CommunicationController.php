<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notice;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommunicationController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * List notices for notice board.
     */
    public function notices(Request $request): JsonResponse
    {
        $query = Notice::with(['author:id,name', 'targetClass'])
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');

        $notices = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notices->items(),
            'meta' => [
                'current_page' => $notices->currentPage(),
                'total' => $notices->total(),
            ],
        ]);
    }

    /**
     * Publish a new circular/notice (Admin / Teacher).
     */
    public function storeNotice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'audience_type' => 'required|in:all,teachers,students,parents,class',
            'target_class_id' => 'nullable|exists:school_classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $notice = Notice::create([
            'created_by_user_id' => $request->user()->id,
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'audience_type' => $request->input('audience_type', 'all'),
            'target_class_id' => $request->input('target_class_id'),
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->auditLogService->log(
            event: 'notice.published',
            auditable: $notice,
            newValues: ['title' => $notice->title, 'audience' => $notice->audience_type],
            request: $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Notice published successfully to Notice Board.',
            'data' => $notice->load('author:id,name'),
        ], 201);
    }

    /**
     * Get direct message thread between authenticated user and another user.
     */
    public function messages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'peer_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $peerId = (int) $request->input('peer_user_id');

        $messages = Message::with(['sender:id,name,avatar_url', 'receiver:id,name,avatar_url'])
            ->where(function ($query) use ($userId, $peerId) {
                $query->where(function ($q) use ($userId, $peerId) {
                    $q->where('sender_user_id', $userId)->where('receiver_user_id', $peerId);
                })->orWhere(function ($q) use ($userId, $peerId) {
                    $q->where('sender_user_id', $peerId)->where('receiver_user_id', $userId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        // Mark incoming unread messages as read
        Message::where('sender_user_id', $peerId)
            ->where('receiver_user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Send direct message.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_user_id' => 'required|exists:users,id',
            'message_body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $msg = Message::create([
            'sender_user_id' => $request->user()->id,
            'receiver_user_id' => $request->input('receiver_user_id'),
            'message_body' => $request->input('message_body'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent.',
            'data' => $msg->load(['sender:id,name', 'receiver:id,name']),
        ], 201);
    }
}
