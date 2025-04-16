<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 * name="User Profile & Settings",
 * description="API endpoints for managing user profiles and settings"
 * )
 */
class UserProfileController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/profile",
     * summary="Get authenticated user's profile information",
     * tags={"User Profile & Settings"},
     * security={{"sanctum": {}}},
     * @OA\Response(
     * response=200,
     * description="User profile data",
     * @OA\JsonContent(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * // Add other profile fields as needed
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function getProfile(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * @OA\Put(
     * path="/api/profile",
     * summary="Update authenticated user's profile information",
     * tags={"User Profile & Settings"},
     * security={{"sanctum": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", maxLength=255, example="Jane Doe"),
     * // Add other updatable profile fields with their types and examples
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Profile updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Profile updated successfully")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation errors",
     * @OA\JsonContent(
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            // Add validation rules for other updatable fields
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        // Update other profile fields based on the request

        $user->save();

        return response()->json(['message' => 'Profile updated successfully']);
    }

    /**
     * @OA\Put(
     * path="/api/settings",
     * summary="Update authenticated user's settings",
     * tags={"User Profile & Settings"},
     * security={{"sanctum": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="notification_preferences", type="object", example={"email": true, "push": false}),
     * // Add other settings fields
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Settings updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Settings updated successfully")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation errors",
     * @OA\JsonContent(
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'notification_preferences.email' => 'sometimes|boolean',
            'notification_preferences.push' => 'sometimes|boolean',
            // Add validation rules for other settings fields
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('notification_preferences')) {
            $user->settings = json_encode($request->notification_preferences); // Assuming a 'settings' JSON column in your users table
        }
        // Update other settings based on the request

        $user->save();

        return response()->json(['message' => 'Settings updated successfully']);
    }

    // If handling password update here (consider using Fortify)
    /*
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Invalid old password'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
    */
}