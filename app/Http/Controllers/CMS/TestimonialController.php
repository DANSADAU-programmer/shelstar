<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Testimonial::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048', // Adjust as needed
            'is_approved' => 'boolean',
        ]);

        $testimonial = Testimonial::create($request->all());

        return response()->json($testimonial, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        return $testimonial;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'name' => 'nullable|max:255',
            'content' => 'nullable',
            'image' => 'nullable|image|max:2048', // Adjust as needed
            'is_approved' => 'nullable|boolean',
        ]);

        $testimonial->update($request->all());

        return response()->json($testimonial);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return response()->json(['message' => 'Testimonial deleted']);
    }
}