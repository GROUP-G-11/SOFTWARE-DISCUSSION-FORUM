<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Topic;
use App\Models\TopicRecommendation;
use App\Services\TopicClassifierService;
use Illuminate\Http\Request;

/**
 * ML Classification and Recommendation module (SDD 5.8).
 *
 * Topic classification happens automatically on creation (see
 * TopicController::store). This controller covers the recommendation
 * feed: content-based + collaborative filtering against unseen topics.
 */
class RecommendationController extends Controller
{
    public function __construct(private TopicClassifierService $classifier)
    {
    }

    /** Home feed recommendations for the authenticated user. */
    public function index(Request $request)
    {
        $user = $request->user();

        $seenTopicIds = Post::where('author_id', $user->user_id)->pluck('topic_id');
        $categoriesEngaged = Topic::whereIn('topic_id', $seenTopicIds)->pluck('category')->countBy();

        $groupIds = $user->groups()->pluck('groups.group_id');

        $unseenTopics = Topic::whereIn('group_id', $groupIds)
            ->whereNotIn('topic_id', $seenTopicIds)
            ->latest()
            ->limit(50)
            ->get();

        $recommendations = $unseenTopics->map(function (Topic $topic) use ($user, $categoriesEngaged) {
            $matches = $categoriesEngaged->get($topic->category, 0);
            $score = $this->classifier->relevanceScore($matches, max($categoriesEngaged->sum(), 1));

            return TopicRecommendation::updateOrCreate(
                ['user_id' => $user->user_id, 'topic_id' => $topic->topic_id],
                ['relevance_score' => $score, 'generated_at' => now()]
            );
        })->sortByDesc('relevance_score')->values()->take(10);

        return response()->json($recommendations->load('topic'));
    }
}
