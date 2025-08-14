<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function like(Post $post)
    {
        $userId = Auth::id();

        if ($post->likes()->where('user_id', $userId)->exists()) {
            return response()->json(['message' => 'Already liked'], 409);
        }

        $post->likes()->create([
            'user_id' => $userId,
        ]);

        return $this->formatPostResponse($post, $userId, true);
    }

    public function unlike(Post $post)
    {
        $userId = Auth::id();

        $deleted = $post->likes()->where('user_id', $userId)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Not liked yet'], 404);
        }

        return $this->formatPostResponse($post, $userId, false);
    }

    /**
     * Format post response with updated likes_count and liked status
     */
    private function formatPostResponse(Post $post, $userId, $likedStatus)
    {
        $post->load('user:id,name')->loadCount('likes');

        if ($post->featured_image && !str_starts_with($post->featured_image, 'http')) {
            $post->featured_image = asset('storage/' . $post->featured_image);
        }

        $post->liked = $likedStatus;

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }
}
