<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->where('user_id', $request->user()->id)
            ->withCount('tasks')
            ->when($request->search, fn ($query, $search) =>
                $query->where('name', 'like', "%{$search}%")
            )
            ->when($request->status, fn ($query, $status) =>
                $query->where('status', $status)
            )
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects)->response();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'sometimes|in:active,completed,archived',
        ]);

        $project = Project::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new ProjectResource($project->loadCount('tasks')));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'sometimes|in:active,completed,archived',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($project),
        ]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
