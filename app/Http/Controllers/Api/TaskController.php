<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $tasks = Task::query()
            ->where('project_id', $project->id)
            ->with('tags')
            ->when($request->search, fn ($query, $search) =>
                $query->where('title', 'like', "%{$search}%")
            )
            ->when($request->status, fn ($query, $status) =>
                $query->where('status', $status)
            )
            ->when($request->priority, fn ($query, $priority) =>
                $query->where('priority', $priority)
            )
            ->latest()
            ->paginate(15);

        return TaskResource::collection($tasks)->response();
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'priority' => $validated['priority'] ?? 'medium',
            'due_date' => $validated['due_date'] ?? null,
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
        ]);

        if (!empty($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'message' => 'Task created successfully',
            'data' => new TaskResource($task->load('tags')),
        ], 201);
    }

    public function show(Request $request, Project $project, Task $task): JsonResponse
    {
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(new TaskResource($task->load('tags')));
    }

    public function update(Request $request, Project $project, Task $task): JsonResponse
    {
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $task->update(collect($validated)->except('tag_ids')->toArray());

        if (isset($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task->load('tags')),
        ]);
    }

    public function destroy(Request $request, Project $project, Task $task): JsonResponse
    {
        if ($project->user_id !== $request->user()->id || $task->project_id !== $project->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
