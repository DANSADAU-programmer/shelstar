<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts|max:255',
            'content' => 'required',
            'image' => 'nullable|image|max:2048', // Adjust as needed
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:draft,published',
            'seo_metadata' => 'nullable|array',
            'seo_metadata.title' => 'nullable|string|max:255',
            'seo_metadata.description' => 'nullable|string',
            'seo_metadata.keywords' => 'nullable|string',
        ]);

        $post = Post::create($request->all());

        // Handle image upload using Spatie Media Library later

        return response()->json($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $post;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'nullable|max:255',
            'slug' => ['nullable', 'max:255', Rule::unique('posts')->ignore($post)],
            'content' => 'nullable',
            'image' => 'nullable|image|max:2048', // Adjust as needed
            'user_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:draft,published',
            'seo_metadata' => 'nullable|array',
            'seo_metadata.title' => 'nullable|string|max:255',
            'seo_metadata.description' => 'nullable|string',
            'seo_metadata.keywords' => 'nullable|string',
        ]);

        $post->update($request->all());

        // Handle image update using Spatie Media Library later

        return response()->json($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}