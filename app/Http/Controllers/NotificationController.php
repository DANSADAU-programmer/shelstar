<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 * name="Notifications",
 * description="API endpoints for managing user notifications"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/notifications",
     * summary="Get authenticated user's notifications",
     * tags={"Notifications"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Page number for pagination",
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="List of user notifications",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * @OA\Property(property="id", type="string", format="uuid", example="a1b2c3d4-e5f6-7890-1234-567890abcdef"),
     * @OA\Property(property="type", type="string", example="App\\Notifications\\NewMessage"),
     * @OA\Property(property="data", type="object", example={"message": "Hello!"}),
     * @OA\Property(property="read_at", type="string", format="date-time", nullable=true),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(20); // Adjust pagination as needed

        return response()->json($notifications->items()); // Only return the items for simplicity
        // If you need pagination meta data, return the entire $notifications object
    }

    /**
     * @OA\Put(
     * path="/api/notifications/{id}/mark-as-read",
     * summary="Mark a specific notification as read",
     * tags={"Notifications"},
     * security={{"sanctum": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the notification to mark as read",
     * @OA\Schema(type="string", format="uuid", example="a1b2c3d4-e5f6-7890-1234-567890abcdef")
     * ),
     * @OA\Response(
     * response=200,
     * description="Notification marked as read successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Notification marked as read")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Notification not found or does not belong to the user",
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * @OA\Put(
     * path="/api/notifications/mark-all-as-read",
     * summary="Mark all authenticated user's notifications as read",
     * tags={"Notifications"},
     * security={{"sanctum": {}}},
     * @OA\Response(
     * response=200,
     * description="All notifications marked as read successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="All notifications marked as read")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}