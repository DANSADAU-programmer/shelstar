<?php

namespace App\Http\Controllers;

use App\Models\KYCDocument;
use App\Models\KYCSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 * name="KYC",
 * description="API endpoints for Know Your Customer (KYC) verification"
 * )
 */
class KYCController extends Controller
{
    /**
     * Submit KYC information and documents.
     */
    public function submit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'personal_information' => 'required|json',
            'document_type' => 'required|string|in:national_id,passport,drivers_license,utility_bill',
            'document' => 'required|file|mimes:jpeg,png,pdf|max:2048', // Adjust mime types and size
            // Add validation for other potential fields
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $submission = KYCSubmission::firstOrCreate(['user_id' => $user->id]);
        $submission->personal_information = json_decode($request->personal_information, true);
        $submission->save();

        $path = $request->file('document')->store('kyc_documents/' . $user->id, 'public');

        // If using a separate kyc_documents table
        KYCDocument::create([
            'kyc_submission_id' => $submission->id,
            'document_type' => $request->document_type,
            'file_path' => $path,
        ]);

        return response()->json(['message' => 'KYC submission successful']);
    }

    /**
     * Get the KYC verification status of the authenticated user.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $submission = KYCSubmission::where('user_id', $user->id)->first();

        if ($submission) {
            return response()->json(['status' => $submission->status]);
        }

        return response()->json(['status' => 'not_submitted']);
    }

    /**
     * Get the uploaded KYC documents of the authenticated user.
     */
    public function getDocuments(Request $request): JsonResponse
    {
        $user = $request->user();
        $submission = KYCSubmission::where('user_id', $user->id)->first();

        if ($submission && isset($submission->documents)) {
            $documents = $submission->documents->map(function ($doc) {
                return [
                    'document_type' => $doc->document_type,
                    'file_url' => Storage::url($doc->file_path),
                ];
            });
            return response()->json($documents);
        }

        return response()->json([]);
    }
}