<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @OA\Tag(
 * name="Authentication",
 * description="API endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/register",
     * summary="Register a new user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", minLength=8, example="password123"),
     * @OA\Property(property="password_confirmation", type="string", minLength=8, example="password123")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully",
     * @OA\JsonContent(
     * @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     * @OA\Property(property="token_type", type="string", example="bearer")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation errors",
     * @OA\JsonContent(
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field is required.")),
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
     * @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
     * )
     * )
     * )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Optionally assign a default role to the user here
        // $user->assignRole('user');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'bearer'], 201);
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Log in an existing user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "password"},
     * @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", minLength=8, example="password123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="User logged in successfully",
     * @OA\JsonContent(
     * @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     * @OA\Property(property="token_type", type="string", example="bearer")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid login credentials",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Invalid login credentials")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation errors",
     * @OA\JsonContent(
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
     * @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
     * )
     * )
     * )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'bearer'], 200);
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * summary="Log out the current user",
     * tags={"Authentication"},
     * security={{"sanctum": {}}},
     * @OA\Response(
     * response=200,
     * description="Successfully logged out",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Successfully logged out")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Get(
     * path="/api/user",
     * summary="Get the authenticated user's data",
     * tags={"Authentication"},
     * security={{"sanctum": {}}},
     * @OA\Response(
     * response=200,
     * description="Authenticated user data",
     * @OA\JsonContent(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", example="john.doe@example.com"),
     * // Add other user properties as needed
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * )
     * )
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}