<?php 

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000'
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content']
        ]);

        return response()->json([
            'success' => true,
            'data' => $comment->load('user:id,name')
        ], 201);
    }

    public function index(Post $post)
    {
        $comments = $post->comments()->with('user:id,name')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }
}
