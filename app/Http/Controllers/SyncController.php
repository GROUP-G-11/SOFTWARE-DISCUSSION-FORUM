<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SyncRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    /**
     * Synchronize offline messages.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'last_synced_at' => 'nullable|date',
            'offline_posts' => 'array'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Push Offline Posts
        |--------------------------------------------------------------------------
        */

        if ($request->has('offline_posts')) {

            foreach ($request->offline_posts as $message) {

                Post::create([
                    'topic_id' => $message['topic_id'],
                    'author_id' => Auth::id(),
                    'content' => $message['content'],
                    'attachment_url' => $message['attachment_url'] ?? null,
                    'posted_at' => $message['posted_at'],
                    'is_flagged' => false,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pull New Posts
        |--------------------------------------------------------------------------
        */

        $posts = Post::with('author')
            ->when($request->last_synced_at, function ($query) use ($request) {
                $query->where('posted_at', '>', $request->last_synced_at);
            })
            ->orderBy('posted_at')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Update Sync Record
        |--------------------------------------------------------------------------
        */

        SyncRecord::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'device_type' => 'Desktop'
            ],
            [
                'last_synced_at' => now(),
                'pending_actions' => []
            ]
        );

        return response()->json([
            'status' => true,
            'last_synced_at' => now(),
            'posts' => $posts
        ]);
    }
}
