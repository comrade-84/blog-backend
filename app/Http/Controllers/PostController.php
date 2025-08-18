<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $userId = Auth::id();
    $sort = $request->query('sort', 'latest'); // default sort
    $perPage = $request->query('per_page') === 'all' ? null : (int) $request->query('per_page', 10); // handle 'all' or default to 10
    $userPosts = $request->query('user_posts', false); // filter for user's posts
    $status = $request->query('status'); // filter by status (draft/published)

    $query = Post::with(['user:id,name,avatar'])
        ->withCount(['likes', 'comments']);

    // Always filter by user for getUserPosts endpoint
    if ($request->is('api/posts/user') || $userPosts) {
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $query->where('user_id', $userId);
    }

    // Filter by status if specified
    if ($status) {
        $query->where('status', $status);
    }

    // Apply sorting
    $query->orderBy(
        match ($sort) {
            'likes' => 'likes_count',
            'views' => 'views_count',
            'comments' => 'comments_count',
            default => 'created_at',
        },
        'desc'
    );

    $posts = $perPage ? $query->paginate($perPage) : $query->get();

    // Append full URL for images + liked status
    $collection = $posts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $posts->getCollection() : $posts;
    $collection->transform(function ($post) use ($userId) {
        if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
            $post->featured_image = asset('storage/' . $post->featured_image);
        }
        if ($post->user && $post->user->avatar) {
            $post->user->avatar_url = asset('storage/' . $post->user->avatar);
        }
        $post->liked = $userId ? $post->likes()->where('user_id', $userId)->exists() : false;
        return $post;
    });

    return response()->json([
        'success' => true,
        'data' => $posts
    ]);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'in:draft,published',
            'featured_image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = $path; // store relative path
        }

        $validated['user_id'] = Auth::id();

        $post = Post::create($validated);

        // Return with full URL
        if ($post->featured_image) {
            $post->featured_image = asset('storage/' . $post->featured_image);
        }

        $post->likes_count = 0;
        $post->liked = false;

        return response()->json(['success' => true, 'data' => $post], 201);
    }

    /**
     * Display the specified resource.
     */
public function show(string $id)
{
    $userId = Auth::id();

    $post = Post::with('user:id,name', 'comments.user:id,name') // include comments
        ->withCount('likes')
        ->findOrFail($id);

    // Increment views count
    $post->increment('views_count');

    if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
        $post->featured_image = asset('storage/' . $post->featured_image);
    }

    $post->liked = $userId ? $post->likes()->where('user_id', $userId)->exists() : false;

    return response()->json(['success' => true, 'data' => $post]);
}



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draft,published',
            'featured_image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($post->featured_image && str_contains($post->featured_image, 'posts/')) {
                Storage::disk('public')->delete($post->featured_image);
            }

            $path = $request->file('featured_image')->store('posts', 'public');
            $validated['featured_image'] = $path;
        }

        $post->update($validated);

        if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
            $post->featured_image = asset('storage/' . $post->featured_image);
        }

        $post->loadCount('likes');
        $post->liked = Auth::check() ? $post->likes()->where('user_id', Auth::id())->exists() : false;

        return response()->json(['success' => true, 'data' => $post]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete image if exists
        if ($post->featured_image && str_contains($post->featured_image, 'posts/')) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return response()->json(['success' => true, 'message' => 'Post deleted successfully']);
    }
    
}
