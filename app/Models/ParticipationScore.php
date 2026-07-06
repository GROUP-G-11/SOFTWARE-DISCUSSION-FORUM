<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ParticipationScore - marks a student earns for taking part in the discussion forum.
 * See SDD 4.2 "ParticipationScore" table.
 */
class ParticipationScore extends Model
{
    protected $table = 'participation_scores';
    protected $primaryKey = 'score_id';

    protected $fillable = ['user_id', 'group_id', 'criteria_id', 'points_earned', 'last_updated'];

    protected $casts = ['last_updated' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(ScoringCriteria::class, 'criteria_id', 'criteria_id');
    }
}
