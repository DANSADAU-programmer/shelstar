<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Page::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:pages|max:255',
            'content' => 'nullable',
            'status' => 'required|in:draft,published',
            'seo_metadata' => 'nullable|array',
            'seo_metadata.title' => 'nullable|string|max:255',
            'seo_metadata.description' => 'nullable|string',
            'seo_metadata.keywords' => 'nullable|string',
        ]);

        $page = Page::create($request->all());

        return response()->json($page, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page)
    {
        return $page;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'nullable|max:255',
            'slug' => ['nullable', 'max:255', Rule::unique('pages')->ignore($page)],
            'content' => 'nullable',
            'status' => 'nullable|in:draft,published',
            'seo_metadata' => 'nullable|array',
            'seo_metadata.title' => 'nullable|string|max:255',
            'seo_metadata.description' => 'nullable|string',
            'seo_metadata.keywords' => 'nullable|string',
        ]);

        $page->update($request->all());

        return response()->json($page);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json(['message' => 'Page deleted']);
    }
}