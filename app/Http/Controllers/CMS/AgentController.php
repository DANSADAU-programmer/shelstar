<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Agent::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048', // Adjust as needed
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $agent = Agent::create($request->all());

        return response()->json($agent, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent)
    {
        return $agent;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'name' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048', // Adjust as needed
            'status' => 'nullable|in:active,inactive', // Added status
        ]);

        $agent->update($request->all());

        return response()->json($agent);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent)
    {
        $agent->delete();
        return response()->json(['message' => 'Agent deleted']);
    }
}