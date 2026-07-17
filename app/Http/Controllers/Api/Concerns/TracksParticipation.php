<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\ParticipationScore;
use App\Models\ScoringCriteria;
use App\Models\User;

/**
 * Shared helper implementing the participation half of the Grading and
 * Participation Module (SDD 5.6): "logs the activity, increments the
 * student's total post count... updates the student's entry in the
 * central relational grade and participation tables."
 */
trait TracksParticipation
{
    protected function recordParticipation(User $user, int $groupId, string $activityType): void
    {
        $criteria = ScoringCriteria::where('group_id', $groupId)
            ->where('activity_type', $activityType)
            ->first();

        if (! $criteria) {
            return; // No scoring rule defined for this group/activity yet.
        }

        $score = ParticipationScore::firstOrCreate(
            ['user_id' => $user->user_id, 'group_id' => $groupId, 'criteria_id' => $criteria->criteria_id],
            ['points_earned' => 0]
        );

        // Each qualifying activity earns marks up to the rule's max, mirroring
        // "marks per activity, and the maximum marks a student earns under this rule."
        $increment = min($criteria->max_marks / 10, $criteria->max_marks - $score->points_earned);
        $score->update([
            'points_earned' => max(0, $score->points_earned + max($increment, 0)),
            'last_updated' => now(),
        ]);
    }
}
