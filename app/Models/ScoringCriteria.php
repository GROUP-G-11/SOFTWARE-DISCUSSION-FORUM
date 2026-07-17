<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ScoringCriteria - a marking rule defined by a lecturer for a group.
 * See SDD 4.2 "Scoring Criteria" table and 5.6 Grading and Participation Module.
 */
class ScoringCriteria extends Model
{
    protected $table = 'scoring_criteria';
    protected $primaryKey = 'criteria_id';

    protected $fillable = ['group_id', 'lecturer_id', 'description', 'activity_type', 'max_marks'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id', 'user_id');
    }

    public function participationScores(): HasMany
    {
        return $this->hasMany(ParticipationScore::class, 'criteria_id', 'criteria_id');
    }
}
