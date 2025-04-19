<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Feature::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:features|max:255',
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $feature = Feature::create($request->all());

        return response()->json($feature, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Feature $feature)
    {
        return $feature;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feature $feature)
    {
        $request->validate([
            'name' => ['nullable', 'max:255', \Illuminate\Validation\Rule::unique('features')->ignore($feature)],
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $feature->update($request->all());

        return response()->json($feature);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feature $feature)
    {
        $feature->delete();
        return response()->json(['message' => 'Feature deleted']);
    }
}