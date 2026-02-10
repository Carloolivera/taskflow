<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::withCount('tasks')->latest()->get();

        return response()->json(TagResource::collection($tags));
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json(new TagResource($tag->loadCount('tasks')));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags',
            'color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'message' => 'Tag created successfully',
            'data' => new TagResource($tag),
        ], 201);
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50|unique:tags,name,' . $tag->id,
            'color' => 'sometimes|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag->update($validated);

        return response()->json([
            'message' => 'Tag updated successfully',
            'data' => new TagResource($tag),
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
