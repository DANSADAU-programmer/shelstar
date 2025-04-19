<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Property::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:properties|max:255',
            'description' => 'nullable',
            'type' => 'required|in:sale,rent,lot',
            'price' => 'nullable|numeric|min:0',
            'address' => 'required|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'size' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:10',
            'category_id' => 'nullable|exists:property_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'agent_id' => 'nullable|exists:agents,id',
            'is_featured' => 'boolean',
            'status' => 'required|in:draft,published,archived',
            'seo_metadata.title' => 'nullable|string|max:255',
            'seo_metadata.description' => 'nullable|string',
            'seo_metadata.keywords' => 'nullable|string',
            'features' => 'nullable|array|exists:features,id',
            // Add validation for media uploads here later
        ]);

        $property = Property::create($request->all());

        if ($request->has('features')) {
            $property->features()->attach($request->input('features'));
        }

        // Handle media uploads here later

        return response()->json($property, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        return $property;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $request->validate([
            'title' => 'nullable|max:255',
            'slug' => ['nullable', 'max:255', Rule::unique('properties')->ignore($property)],
            'description' => 'nullable',
            'type' => 'nullable|in:sale,rent,lot',
            'price' => 'nullable|numeric|min:0',
            'address' => 'nullable|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'size' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:10',
            'category_id' => 'nullable|exists:property_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'agent_id' => 'nullable|exists:agents,id',
            'is_featured' => 'nullable|boolean',
            'status' => 'nullable|in:draft,published,archived',
            'seo_metadata.title' => 'nullable|string|max:255',
            'seo_metadata.description' => 'nullable|string',
            'seo_metadata.keywords' => 'nullable|string',
            'features' => 'nullable|array|exists:features,id',
            // Add validation for media uploads here later
        ]);

        $property->update($request->all());

        if ($request->has('features')) {
            $property->features()->sync($request->input('features'));
        }

        // Handle media updates here later

        return response()->json($property);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();
        return response()->json(['message' => 'Property deleted']);
    }
}