<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ParticipationScore;
use App\Models\QuizAttempt;
use App\Models\ScoringCriteria;
use Illuminate\Http\Request;

/**
 * Grading and Participation Module (SDD 5.6) - lecturer-facing scoring
 * rule management. Instant grade updates themselves happen inline in
 * PostController/ReplyController/QuizAttemptController via TracksParticipation.
 */
class GradingController extends Controller
{
    public function storeCriteria(Request $request, Group $group)
    {
        $request->validate([
            'description' => 'required|string',
            'activity_type' => 'required|in:post,reply,quiz_attempt,topic_creation',
            'max_marks' => 'required|numeric|min:0',
        ]);

        $criteria = ScoringCriteria::create([
            'group_id' => $group->group_id,
            'lecturer_id' => $request->user()->user_id,
            'description' => $request->description,
            'activity_type' => $request->activity_type,
            'max_marks' => $request->max_marks,
        ]);

        return response()->json($criteria, 201);
    }

    public function criteriaIndex(Group $group)
    {
        return response()->json($group->scoringCriteria);
    }

    /** Students' performance evaluation use case (SDD Table 41): view a group's leaderboard. */
    public function leaderboard(Group $group)
    {
        $scores = $group->memberships()
            ->join('users', 'users.user_id', '=', 'memberships.user_id')
            ->selectRaw('users.user_id, users.full_name, COALESCE(SUM(participation_scores.points_earned), 0) as total_points')
            ->leftJoin('participation_scores', function ($j) use ($group) {
                $j->on('participation_scores.user_id', '=', 'memberships.user_id')
                    ->where('participation_scores.group_id', $group->group_id);
            })
            ->groupBy('users.user_id', 'users.full_name')
            ->orderByDesc('total_points')
            ->get();

        return response()->json($scores);
    }

    /**
     * A single student's own grade breakdown for a group: their participation
     * points per scoring criterion, plus their quiz attempt scores for any
     * quizzes belonging to this group. Powers the "My Grades" dashboard view.
     */
    public function myGrade(Request $request, Group $group)
    {
        $user = $request->user();

        $participation = ParticipationScore::with('criteria')
            ->where('group_id', $group->group_id)
            ->where('user_id', $user->user_id)
            ->get();

        $participationTotal = $participation->sum('points_earned');

        $quizAttempts = QuizAttempt::with('quiz')
            ->where('user_id', $user->user_id)
            ->whereHas('quiz', fn ($q) => $q->where('group_id', $group->group_id))
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->get();

        $quizTotal = $quizAttempts->sum('score');

        return response()->json([
            'group' => $group->name,
            'participation' => $participation,
            'participation_total' => $participationTotal,
            'quiz_attempts' => $quizAttempts,
            'quiz_total' => $quizTotal,
            'overall_total' => $participationTotal + $quizTotal,
        ]);
    }

    /**
     * Lecturer/Admin-facing gradebook: every member of the group with their
     * participation total and quiz total side by side, so a lecturer can see
     * the whole class's standing from one screen (links from the dashboard).
     */
    public function gradebook(Group $group)
    {
        $members = $group->members()->get(['users.user_id', 'users.full_name']);

        $participationByUser = ParticipationScore::where('group_id', $group->group_id)
            ->selectRaw('user_id, SUM(points_earned) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $quizByUser = QuizAttempt::whereHas('quiz', fn ($q) => $q->where('group_id', $group->group_id))
            ->whereNotNull('submitted_at')
            ->selectRaw('user_id, SUM(score) as total, COUNT(*) as attempts_count')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $rows = $members->map(function ($member) use ($participationByUser, $quizByUser) {
            $participationTotal = (float) ($participationByUser[$member->user_id] ?? 0);
            $quizRow = $quizByUser->get($member->user_id);
            $quizTotal = $quizRow ? (float) $quizRow->total : 0;
            $attemptsCount = $quizRow ? (int) $quizRow->attempts_count : 0;

            return [
                'user_id' => $member->user_id,
                'full_name' => $member->full_name,
                'participation_total' => $participationTotal,
                'quiz_total' => $quizTotal,
                'quiz_attempts_count' => $attemptsCount,
                'overall_total' => $participationTotal + $quizTotal,
            ];
        })->sortByDesc('overall_total')->values();

        return response()->json([
            'group' => $group->name,
            'rows' => $rows,
        ]);
    }
}
