<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SocialShare;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Social Media Sharing Module (SDD 5.9) - "Forward Post to External Social
 * Media Platform" use case (Table 45).
 */
class SocialShareController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'platform' => 'required|in:WhatsApp,Twitter,Facebook,LinkedIn,Clipboard,Other',
        ]);

        // Step 2 (alternative flow): a post with active exclusions cannot be
        // shared externally, mirroring the SDD's exclusion safeguard.
        if ($post->is_flagged || $post->exclusions()->exists()) {
            return response()->json(['message' => 'This content cannot be shared externally.'], 403);
        }

        $shareUrl = url("/share/{$post->post_id}");

        //$shareUrl = url("/topics/{$post->topic_id}#post-{$post->post_id}");

        $share = SocialShare::create([
            'post_id' => $post->post_id,
            'user_id' => $request->user()->user_id,
            'platform' => $request->platform,
            'shared_url' => $shareUrl,
            'shared_at' => now(),
        ]);

        return response()->json($share, 201);
    }
    
}
