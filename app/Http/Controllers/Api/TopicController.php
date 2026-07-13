<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Services\TopicClassifierService;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

 // Ensure you have barryvdh/laravel-dompdf installed

/**
 * Topic Management and Export Module (SDD 5.3).
 *
 * Lets a member launch a new discussion thread (auto-classified by the ML
 * module), view a topic-focused filtered stream of posts, and export a
 * full topic thread to PDF.
 */
class TopicController extends Controller
{
    public function __construct(private TopicClassifierService $classifier)
    {
    }

    public function index( Request $request, Group $group)
    {
        // Prudence------
         // Topic-Focused View is scoped to a group; only members of that
        // group (i.e. students in the same class group) may browse its topics.
        if (! $request->user()->isMemberOf($group->group_id)) {
            return response()->json(['message' => 'You must be a member of this group to view its topics.'], 403);
        }

        return response()->json(
            $group->topics()->withCount('posts')->latest()->paginate(20)
        );
    }
    
    public function downloadPdf(Request $request, Topic $topic)
    {
        $userId = $request->user()?->user_id ?? auth()->id();

        // Eager load data securely by filtering out selective communication exclusions
        $topic->load([
            'group', 
            'creator', 
            'posts' => function ($query) use ($userId) {
                $query->whereDoesntHave('exclusions', fn ($q) => $q->where('excluded_user_id', $userId))
                      ->with(['author', 'replies.author']);
            }
        ]);

        // Load the view and inject our topic data
        $pdf = Pdf::loadView('forum.topic_export', compact('topic'));

        // Generate a clean slug-based filename based on the topic title
        $filename = Str::slug($topic->title) . '-discussion-history.pdf';

        // Return PDF file download response directly to browser
        return $pdf->download($filename);
    }

    /** Launching new discussion topic thread use case (SDD Table 33). */
    public function store(Request $request, Group $group)
    {
        $request->validate(['title' => 'required|string|max:255']);

        //prudence-----------
           if (! $request->user()->isMemberOf($group->group_id)) {
            return response()->json(['message' => 'You must be a member of this group to start a topic.'], 403);
        }

        if ($request->user()->isBlacklistedIn($group->group_id)) {
            return response()->json(['message' => 'You are blacklisted from posting in this group.'], 403);
        }

        $topic = Topic::create([
            'group_id' => $group->group_id,
            'title' => $request->title,
            'created_by' => $request->user()->user_id,
            'category' => $this->classifier->classify($request->title),
        ]);

        return response()->json($topic, 201);
    }

    /** Topic-Focused View: only chats belonging to this topic, isolated from unrelated discussion. */
    public function show(Request $request, Topic $topic)
    {
        // ----------prudence------
          if (! $request->user()->isMemberOf($topic->group_id)) {
            return response()->json(['message' => 'You must be a member of this topic\'s group to view it.'], 403);
        }

        $userId = $request->user()->user_id;

        // Selective communication filter: hide thread posts that exclude the active viewer
        return response()->json(
            $topic->load([
                'creator', 
                'posts' => function ($query) use ($userId) {
                    $query->whereDoesntHave('exclusions', fn ($q) => $q->where('excluded_user_id', $userId))
                          ->with(['author', 'replies.author']);
                }
            ])
        );
    }

    /** Post export use case (SDD Table 34): export a filtered topic thread to PDF. */
    public function export(Request $request, Topic $topic)
    {
        $userId = $request->user()?->user_id ?? auth()->id();

        $topic->load([
            'creator', 
            'group', 
            'posts' => function ($query) use ($userId) {
                $query->whereDoesntHave('exclusions', fn ($q) => $q->where('excluded_user_id', $userId))
                      ->with(['author', 'replies.author']);
            }
        ]);

        $pdf = Pdf::loadView('topics.export', ['topic' => $topic]);

        return $pdf->download("topic-{$topic->topic_id}-{$topic->title}.pdf");
    }
}