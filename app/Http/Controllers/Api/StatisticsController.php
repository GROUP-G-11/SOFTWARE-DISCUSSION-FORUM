<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;

/**
 * Statistics Module (SDD 5.7) - "Check Student Performance and
 * Participation" use case (Table 42). Route-protected to Administrators
 * via 'role:Administrator' middleware.
 */
class StatisticsController extends Controller
{
    /** Aggregates live metrics: total posts, active contributors, banned individuals, unanswered topics. */
    public function groupStatistics(Group $group)
    {
        $totalPosts = Post::whereHas('topic', fn ($q) => $q->where('group_id', $group->group_id))->count();

        $activeContributors = $group->members()
            ->where('last_active_at', '>=', now()->subDays(7))
            ->count();

        $bannedIndividuals = $group->blacklists()->where('end_date', '>', now())->count();

        $unansweredTopics = $group->topics()->doesntHave('posts')->count();

        // "Struggling Students" roster: idle for over 7 days.
        $strugglingStudents = $group->members()
            ->where(function ($q) {
                $q->where('last_active_at', '<', now()->subDays(7))->orWhereNull('last_active_at');
            })
            ->get(['users.user_id', 'users.full_name', 'users.last_active_at']);

        return response()->json([
            'group' => $group->name,
            'total_posts' => $totalPosts,
            'active_contributors' => $activeContributors,
            'banned_individuals' => $bannedIndividuals,
            'unanswered_topics' => $unansweredTopics,
            'struggling_students' => $strugglingStudents,
        ]);
    }
}
