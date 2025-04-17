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
     * @OA\Property(property="phone_number", type="string", nullable=true, example="123-456-7890"),
     * @OA\Property(property="bio", type="string", nullable=true, example="A short bio about the user."),
     * @OA\Property(property="profile_picture", type="string", nullable=true, example="/images/profile.jpg"),
     * @OA\Property(property="location", type="string", nullable=true, example="New York"),
     * @OA\Property(property="website", type="string", nullable=true, format="url", example="https://example.com"),
     * @OA\Property(property="date_of_birth", type="string", nullable=true, format="date", example="1990-01-01"),
     * @OA\Property(property="gender", type="string", nullable=true, example="male"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="settings", type="object", nullable=true, example={"email_notifications": true}),
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
     * @OA\Property(property="name", type="string", maxLength=255, nullable=true, example="Jane Doe"),
     * @OA\Property(property="phone_number", type="string", nullable=true, example="987-654-3210"),
     * @OA\Property(property="bio", type="string", nullable=true, example="Updated bio."),
     * @OA\Property(property="profile_picture", type="string", nullable=true, example="/images/jane_profile.jpg"),
     * @OA\Property(property="location", type="string", nullable=true, example="Los Angeles"),
     * @OA\Property(property="website", type="string", nullable=true, format="url", example="https://jane.com"),
     * @OA\Property(property="date_of_birth", type="string", nullable=true, format="date", example="1995-05-10"),
     * @OA\Property(property="gender", type="string", nullable=true, example="female"),
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
            'phone_number' => 'sometimes|string|nullable',
            'bio' => 'sometimes|string|nullable',
            'profile_picture' => 'sometimes|string|nullable',
            'location' => 'sometimes|string|nullable',
            'website' => 'sometimes|nullable|url',
            'date_of_birth' => 'sometimes|nullable|date',
            'gender' => 'sometimes|nullable|string|in:male,female,other', // Example of allowed values
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }
        if ($request->has('bio')) {
            $user->bio = $request->bio;
        }
        if ($request->has('profile_picture')) {
            $user->profile_picture = $request->profile_picture;
        }
        if ($request->has('location')) {
            $user->location = $request->location;
        }
        if ($request->has('website')) {
            $user->website = $request->website;
        }
        if ($request->has('date_of_birth')) {
            $user->date_of_birth = $request->date_of_birth;
        }
        if ($request->has('gender')) {
            $user->gender = $request->gender;
        }

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
     * @OA\Property(property="notification_preferences", type="object", nullable=true, example={"email_notifications": true, "push_notifications": false, "new_message_notifications": true}),
     * @OA\Property(property="privacy_settings", type="object", nullable=true, example={"profile_visibility": "public", "show_location": false, "allow_direct_messages": true}),
     * @OA\Property(property="appearance_settings", type="object", nullable=true, example={"theme": "dark", "language": "en"}),
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
            'notification_preferences' => 'sometimes|nullable|array',
            'notification_preferences.email_notifications' => 'sometimes|boolean',
            'notification_preferences.push_notifications' => 'sometimes|boolean',
            'notification_preferences.new_message_notifications' => 'sometimes|boolean',
            'privacy_settings' => 'sometimes|nullable|array',
            'privacy_settings.profile_visibility' => 'sometimes|string|in:public,followers,private',
            'privacy_settings.show_location' => 'sometimes|boolean',
            'privacy_settings.allow_direct_messages' => 'sometimes|boolean',
            'appearance_settings' => 'sometimes|nullable|array',
            'appearance_settings.theme' => 'sometimes|string|in:light,dark',
            'appearance_settings.language' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $settings = (array) json_decode($user->settings, true); // Get existing settings as array

        if ($request->has('notification_preferences')) {
            $settings['notification_preferences'] = array_merge(
                $settings['notification_preferences'] ?? [], // Merge with existing or empty array
                $request->notification_preferences
            );
        }
        if ($request->has('privacy_settings')) {
            $settings['privacy_settings'] = array_merge(
                $settings['privacy_settings'] ?? [],
                $request->privacy_settings
            );
        }
        if ($request->has('appearance_settings')) {
            $settings['appearance_settings'] = array_merge(
                $settings['appearance_settings'] ?? [],
                $request->appearance_settings
            );
        }

        $user->settings = json_encode($settings);
        $user->save();

        return response()->json(['message' => 'Settings updated successfully']);
    }
}