<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Location::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:locations|max:255',
            'slug' => 'required|unique:locations|max:255',
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $location = Location::create($request->all());

        return response()->json($location, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        return $location;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => ['nullable', 'max:255', \Illuminate\Validation\Rule::unique('locations')->ignore($location)],
            'slug' => ['nullable', 'max:255', \Illuminate\Validation\Rule::unique('locations')->ignore($location)],
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $location->update($request->all());

        return response()->json($location);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return response()->json(['message' => 'Location deleted']);
    }
}