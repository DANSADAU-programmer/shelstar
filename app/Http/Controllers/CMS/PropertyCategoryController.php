<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PropertyCategory::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:property_categories|max:255',
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $propertyCategory = PropertyCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->input('status', 'active'), // Default to active
        ]);

        return response()->json($propertyCategory, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyCategory $propertyCategory)
    {
        return $propertyCategory;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyCategory $propertyCategory)
    {
        $request->validate([
            'name' => ['nullable', 'max:255', Rule::unique('property_categories')->ignore($propertyCategory)],
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $propertyCategory->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => $request->input('status'),
        ]);

        return response()->json($propertyCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyCategory $propertyCategory)
    {
        $propertyCategory->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}