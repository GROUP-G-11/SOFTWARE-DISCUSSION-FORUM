<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Quiz - one quiz set up by a lecturer. See SDD 4.2 "Quiz" table and 5.5 Quiz Engine Module.
 */
class Quiz extends Model
{
    protected $table = 'quizzes';
    protected $primaryKey = 'quiz_id';

    protected $fillable = ['group_id', 'lecturer_id', 'title', 'status'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id', 'user_id');
    }

    public function configuration(): HasOne
    {
        return $this->hasOne(QuizConfiguration::class, 'quiz_id', 'quiz_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id', 'quiz_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id', 'quiz_id');
    }

    public function totalMarks(): int
    {
        return $this->questions()->sum('marks');
    }
}
